<?php

namespace Plugin\Affiliate\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Controller\AbstractController;
use Plugin\Affiliate\Form\Type\Admin\AffiliateConfigType;
use Plugin\Affiliate\Repository\AffiliateConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * アフィリエイト設定（管理画面）。料率・クッキー期間・確定日数・最低支払額を編集する。
 *
 * @Route("/%eccube_admin_route%/affiliate/config")
 */
class AffiliateConfigController extends AbstractController
{
    private $configRepository;
    private $entityManager;

    public function __construct(
        AffiliateConfigRepository $configRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->configRepository = $configRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("", name="admin_affiliate_config", methods={"GET", "POST"})
     * @Template("@Affiliate/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->get();
        $form = $this->createForm(AffiliateConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($Config);
            $this->entityManager->flush();

            $this->addSuccess('設定を保存しました。', 'admin');

            return $this->redirectToRoute('admin_affiliate_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
