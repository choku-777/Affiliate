<?php

namespace Plugin\Affiliate\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Repository\BaseInfoRepository;
use Knp\Component\Pager\PaginatorInterface;
use Plugin\Affiliate\Entity\Affiliate;
use Plugin\Affiliate\Repository\AffiliateRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * アフィリエイター管理（管理画面）。一覧・承認・却下・停止を行う。
 *
 * @Route("/%eccube_admin_route%/affiliate")
 */
class AffiliateAdminController extends AbstractController
{
    private $affiliateRepository;
    private $entityManager;
    private $mailer;
    private $baseInfo;

    public function __construct(
        AffiliateRepository $affiliateRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        BaseInfoRepository $baseInfoRepository
    ) {
        $this->affiliateRepository = $affiliateRepository;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->baseInfo = $baseInfoRepository->get();
    }

    /**
     * @Route("", name="admin_affiliate", methods={"GET"})
     * @Template("@Affiliate/admin/index.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $searchData = [
            'status' => $request->query->get('status'),
            'name' => $request->query->get('name'),
            'email' => $request->query->get('email'),
        ];

        $qb = $this->affiliateRepository->getQueryBuilderBySearchData($searchData);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return [
            'pagination' => $pagination,
            'searchData' => $searchData,
        ];
    }

    /**
     * @Route("/{id}", name="admin_affiliate_show", requirements={"id":"\d+"}, methods={"GET"})
     * @Template("@Affiliate/admin/show.twig")
     */
    public function show(Affiliate $Affiliate, Request $request)
    {
        return [
            'Affiliate' => $Affiliate,
            'affiliate_url' => $this->buildAffiliateUrl($request, $Affiliate),
        ];
    }

    /**
     * @Route("/{id}/approve", name="admin_affiliate_approve", requirements={"id":"\d+"}, methods={"POST"})
     */
    public function approve(Affiliate $Affiliate, Request $request)
    {
        $this->isTokenValid();

        $Affiliate->setStatus(Affiliate::STATUS_APPROVED);
        $Affiliate->setApprovedDate(new \DateTime());
        $this->entityManager->flush();

        $this->sendApprovalMail($Affiliate, $request);

        $this->addSuccess('承認しました。発行URLを申請者へ通知しました。', 'admin');

        return $this->redirectToRoute('admin_affiliate_show', ['id' => $Affiliate->getId()]);
    }

    /**
     * @Route("/{id}/reject", name="admin_affiliate_reject", requirements={"id":"\d+"}, methods={"POST"})
     */
    public function reject(Affiliate $Affiliate)
    {
        $this->isTokenValid();

        $Affiliate->setStatus(Affiliate::STATUS_REJECTED);
        $this->entityManager->flush();

        $this->addSuccess('却下しました。', 'admin');

        return $this->redirectToRoute('admin_affiliate_show', ['id' => $Affiliate->getId()]);
    }

    /**
     * @Route("/{id}/suspend", name="admin_affiliate_suspend", requirements={"id":"\d+"}, methods={"POST"})
     */
    public function suspend(Affiliate $Affiliate)
    {
        $this->isTokenValid();

        $Affiliate->setStatus(Affiliate::STATUS_SUSPENDED);
        $this->entityManager->flush();

        $this->addSuccess('停止しました。', 'admin');

        return $this->redirectToRoute('admin_affiliate_show', ['id' => $Affiliate->getId()]);
    }

    /**
     * アフィリエイトURL（ショップトップ + ?affiliate=CODE）を組み立てる。
     */
    private function buildAffiliateUrl(Request $request, Affiliate $Affiliate): string
    {
        $base = $request->getSchemeAndHttpHost();

        return $base.'/?affiliate='.$Affiliate->getAffiliateCode();
    }

    /**
     * 承認通知メールを送信する（ベストエフォート）。
     */
    private function sendApprovalMail(Affiliate $Affiliate, Request $request): void
    {
        try {
            $url = $this->buildAffiliateUrl($request, $Affiliate);
            $shopName = $this->baseInfo instanceof BaseInfo ? $this->baseInfo->getShopName() : '';
            $from = $this->baseInfo instanceof BaseInfo ? $this->baseInfo->getEmail01() : null;

            $body = $Affiliate->getName()."様\n\n"
                ."アフィリエイトのお申し込みが承認されました。\n"
                ."以下のURLからのご紹介が成果対象になります。\n\n"
                .$url."\n\n"
                ."※URLをブログやSNS等に掲載してご利用ください。\n";

            $email = (new Email())
                ->subject('【'.$shopName.'】アフィリエイト登録が承認されました')
                ->to($Affiliate->getEmail())
                ->text($body);

            if ($from) {
                $email->from($from);
            }

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            // メール送信失敗は承認処理を妨げない
            log_error('アフィリエイト承認メールの送信に失敗しました。', ['error' => $e->getMessage()]);
        }
    }
}
