<?php

namespace Plugin\Affiliate\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Plugin\Affiliate\Entity\AffiliateReward;
use Plugin\Affiliate\Form\Type\Admin\AffiliateRewardSearchType;
use Plugin\Affiliate\Repository\AffiliateRewardRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * 成果・報酬の集計と支払い管理（管理画面）。実際の振込は手動で行い、状態のみ更新する。
 *
 * @Route("/%eccube_admin_route%/affiliate/reward")
 */
class AffiliateRewardController extends AbstractController
{
    private $rewardRepository;
    private $entityManager;

    public function __construct(
        AffiliateRewardRepository $rewardRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->rewardRepository = $rewardRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("", name="admin_affiliate_reward", methods={"GET"})
     * @Template("@Affiliate/admin/reward.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $searchForm = $this->createForm(AffiliateRewardSearchType::class);
        $searchForm->handleRequest($request);

        $searchData = $searchForm->isSubmitted() && $searchForm->isValid()
            ? $searchForm->getData()
            : [];

        $qb = $this->rewardRepository->getQueryBuilderBySearchData($searchData);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'totals' => $this->rewardRepository->getTotalsByStatus($searchData),
        ];
    }

    /**
     * 確定済みの報酬を支払済にする。
     *
     * @Route("/{id}/pay", name="admin_affiliate_reward_pay", requirements={"id":"\d+"}, methods={"POST"})
     */
    public function pay(AffiliateReward $Reward)
    {
        $this->isTokenValid();

        if ($Reward->getStatus() !== AffiliateReward::STATUS_CONFIRMED) {
            $this->addError('確定済みの報酬のみ支払済にできます。', 'admin');

            return $this->redirectToRoute('admin_affiliate_reward');
        }

        $Reward->setStatus(AffiliateReward::STATUS_PAID);
        $Reward->setPaidDate(new \DateTime());
        $this->entityManager->flush();

        $this->addSuccess('支払済に更新しました。', 'admin');

        return $this->redirectToRoute('admin_affiliate_reward');
    }
}
