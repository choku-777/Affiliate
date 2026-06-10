<?php

namespace Plugin\Affiliate;

use Eccube\Plugin\AbstractPluginManager;
use Psr\Container\ContainerInterface;

/**
 * 本プラグインは計測（postback）専用で、独自テーブルや初期データを持たない。
 * 接続先・APIキー等の設定は EC-CUBE の .env で行う（README参照）。
 */
class PluginManager extends AbstractPluginManager
{
    public function enable(array $meta, ContainerInterface $container)
    {
        // 取りこぼし防止用アウトボックスのディレクトリを用意する
        $dir = $container->getParameter('kernel.project_dir').'/var/affiliate_outbox';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }
}
