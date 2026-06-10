<?php

namespace Plugin\Affiliate\Command;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Master\OrderStatus;
use Plugin\Affiliate\Entity\AffiliateReward;
use Plugin\Affiliate\Repository\AffiliateConfigRepository;
use Plugin\Affiliate\Repository\AffiliateRewardRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 未確定（pending）の成果を確定・取消するバッチ。日次 cron での実行を想定。
 *
 *   bin/console affiliate:confirm-rewards
 *
 * - 注文がキャンセル/返品 → cancelled
 * - 注文発生から confirm_after_days 経過かつ正常 → confirmed
 */
class AffiliateConfirmCommand extends Command
{
    protected static $defaultName = 'affiliate:confirm-rewards';

    private $rewardRepository;
    private $configRepository;
    private $entityManager;

    public function __construct(
        AffiliateRewardRepository $rewardRepository,
        AffiliateConfigRepository $configRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->rewardRepository = $rewardRepository;
        $this->configRepository = $configRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('未確定のアフィリエイト成果を確定・取消します。');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $config = $this->configRepository->get();
        $confirmAfterDays = $config->getConfirmAfterDays();

        $threshold = new \DateTime();
        $threshold->modify('-'.$confirmAfterDays.' day');

        $cancelStatuses = [OrderStatus::CANCEL, OrderStatus::RETURNED];

        $confirmed = 0;
        $cancelled = 0;

        foreach ($this->rewardRepository->findPending() as $reward) {
            $order = $reward->getOrder();

            // 注文が削除済み（参照切れ）の場合は取消扱い
            if ($order === null) {
                $reward->setStatus(AffiliateReward::STATUS_CANCELLED);
                $cancelled++;
                continue;
            }

            $orderStatusId = $order->getOrderStatus() ? $order->getOrderStatus()->getId() : null;

            if (in_array($orderStatusId, $cancelStatuses, true)) {
                $reward->setStatus(AffiliateReward::STATUS_CANCELLED);
                $cancelled++;
                continue;
            }

            // 猶予期間を経過していれば確定
            if ($reward->getConvertedDate() <= $threshold) {
                $reward->setStatus(AffiliateReward::STATUS_CONFIRMED);
                $reward->setConfirmedDate(new \DateTime());
                $confirmed++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('確定: %d件 / 取消: %d件', $confirmed, $cancelled));

        return 0;
    }
}
