<?php

namespace Plugin\Affiliate\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Eccube\Repository\AbstractRepository;
use Plugin\Affiliate\Entity\AffiliateConfig;

class AffiliateConfigRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffiliateConfig::class);
    }

    /**
     * 設定（単一レコード）を取得する。存在しない場合はデフォルト値の新規インスタンスを返す。
     */
    public function get(): AffiliateConfig
    {
        $config = $this->find(1);
        if (!$config) {
            $config = new AffiliateConfig();
        }

        return $config;
    }
}
