<?php

namespace Plugin\Affiliate\Controller\Front;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Controller\AbstractController;
use Plugin\Affiliate\Entity\Affiliate;
use Plugin\Affiliate\Form\Type\Front\AffiliateEntryType;
use Plugin\Affiliate\Repository\AffiliateRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * アフィリエイター登録（フロント）。登録は申請として受け付け、管理者の承認後に有効化される。
 */
class AffiliateController extends AbstractController
{
    private $affiliateRepository;
    private $entityManager;

    public function __construct(
        AffiliateRepository $affiliateRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->affiliateRepository = $affiliateRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/affiliate/entry", name="affiliate_entry", methods={"GET", "POST"})
     * @Template("@Affiliate/entry.twig")
     */
    public function entry(Request $request)
    {
        $Affiliate = new Affiliate();
        $form = $this->createForm(AffiliateEntryType::class, $Affiliate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Affiliate->setAffiliateCode($this->generateUniqueCode());
            $Affiliate->setStatus(Affiliate::STATUS_PENDING);

            $this->entityManager->persist($Affiliate);
            $this->entityManager->flush();

            return $this->redirectToRoute('affiliate_entry_complete');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/affiliate/entry/complete", name="affiliate_entry_complete", methods={"GET"})
     * @Template("@Affiliate/complete.twig")
     */
    public function complete()
    {
        return [];
    }

    /**
     * 衝突しない一意のアフィリエイトコードを生成する。
     */
    private function generateUniqueCode(): string
    {
        do {
            $code = bin2hex(random_bytes(8));
        } while ($this->affiliateRepository->findOneBy(['affiliate_code' => $code]));

        return $code;
    }
}
