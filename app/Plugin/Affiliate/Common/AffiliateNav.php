<?php

namespace Plugin\Affiliate\Common;

use Eccube\Common\EccubeNav;

/**
 * 管理画面のメニューに「アフィリエイト」を追加する。
 */
class AffiliateNav implements EccubeNav
{
    public static function getNav()
    {
        return [
            'affiliate' => [
                'name' => 'アフィリエイト',
                'icon' => 'fa-share-alt',
                'children' => [
                    'affiliate_list' => [
                        'name' => 'アフィリエイター',
                        'url' => 'admin_affiliate',
                    ],
                    'affiliate_reward' => [
                        'name' => '成果・報酬',
                        'url' => 'admin_affiliate_reward',
                    ],
                    'affiliate_config' => [
                        'name' => '設定',
                        'url' => 'admin_affiliate_config',
                    ],
                ],
            ],
        ];
    }
}
