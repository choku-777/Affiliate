<?php

namespace Plugin\Affiliate\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\Affiliate\Entity\AffiliateReward;
use Plugin\Affiliate\Repository\AffiliateConfigRepository;
use Plugin\Affiliate\Repository\AffiliateRepository;
use Plugin\Affiliate\Repository\AffiliateRewardRepository;
use Plugin\Affiliate\Service\RewardCalculator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * 注文完了時にクッキーを参照し、成果（未確定）を記録する。
 */
class ShoppingCompleteListener implements EventSubscriberInterface
{
    private $affiliateRepository;
    private $rewardRepository;
    private $configRepository;
    private $rewardCalculator;
    private $entityManager;

    public function __construct(
        AffiliateRepository $affiliateRepository,
        AffiliateRewardRepository $rewardRepository,
        AffiliateConfigRepository $configRepository,
        RewardCalculator $rewardCalculator,
        EntityManagerInterface $entityManager
    ) {
        $this->affiliateRepository = $affiliateRepository;
        $this->rewardRepository = $rewardRepository;
        $this->configRepository = $configRepository;
        $this->rewardCalculator = $rewardCalculator;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            EccubeEvents::FRONT_SHOPPING_COMPLETE_INITIALIZE => 'onShoppingComplete',
        ];
    }

    public function onShoppingComplete(EventArgs $event)
    {
        $Order = $event->getArgument('Order');
        if (!$Order) {
            return;
        }

        $request = $event->getRequest();
        $code = $request->cookies->get(AffiliateCookieListener::COOKIE_NAME);
        if (!$code) {
            return;
        }

        $affiliate = $this->affiliateRepository->findApprovedByCode($code);
        if (!$affiliate) {
            return;
        }

        // 同一注文での二重記録を防ぐ（完了画面の再表示など）
        if ($this->rewardRepository->existsByOrder($Order)) {
            return;
        }

        $config = $this->configRepository->get();
        $rate = $config->getCommissionRate();
        $orderTotal = $Order->getPaymentTotal();
        $rewardAmount = $this->rewardCalculator->calculate($orderTotal, $rate);

        $reward = new AffiliateReward();
        $reward->setAffiliate($affiliate)
            ->setOrder($Order)
            ->setOrderTotal($orderTotal)
            ->setRateApplied($rate)
            ->setRewardAmount($rewardAmount)
            ->setStatus(AffiliateReward::STATUS_PENDING)
            ->setConvertedDate(new \DateTime());

        $this->entityManager->persist($reward);
        $this->entityManager->flush();
    }
}
