<?php

namespace Plugin\Affiliate;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\Affiliate\Entity\AffiliateConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * プラグインの有効化時に初期設定（料率・クッキー期間・確定日数・最低支払額）を投入する。
 */
class PluginManager extends AbstractPluginManager
{
    public function enable(array $meta, ContainerInterface $container)
    {
        $this->createConfig($container);
    }

    private function createConfig(ContainerInterface $container)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
        $repository = $entityManager->getRepository(AffiliateConfig::class);

        // 既に設定が存在する場合は何もしない（無効化→再有効化で初期化されないように）
        if ($repository->find(1)) {
            return;
        }

        $config = new AffiliateConfig();
        $config->setCommissionRate('5.00');     // 報酬料率（%）
        $config->setCookieLifetimeDays(30);     // 成果紐付けクッキーの有効期間（日）
        $config->setConfirmAfterDays(30);       // 注文発生から確定までの猶予期間（日）
        $config->setMinPayoutAmount(5000);      // 最低支払額（円）

        $entityManager->persist($config);
        $entityManager->flush();
    }
}
