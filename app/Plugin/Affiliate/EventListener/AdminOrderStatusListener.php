<?php

namespace Plugin\Affiliate\EventListener;

use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\Affiliate\Service\Config;
use Plugin\Affiliate\Service\PostbackClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * 管理画面で注文が編集（ステータス変更等）された際、注文番号と現在のステータスを
 * 管理サービスへ送信する。キャンセル・返品時の成果取消を管理サービス側に反映するため。
 */
class AdminOrderStatusListener implements EventSubscriberInterface
{
    private $postbackClient;
    private $config;

    public function __construct(PostbackClient $postbackClient, Config $config)
    {
        $this->postbackClient = $postbackClient;
        $this->config = $config;
    }

    public static function getSubscribedEvents()
    {
        return [
            EccubeEvents::ADMIN_ORDER_EDIT_INDEX_COMPLETE => 'onOrderEditComplete',
        ];
    }

    public function onOrderEditComplete(EventArgs $event)
    {
        // 計測の失敗で管理画面の注文編集を絶対に止めない（保険）
        try {
            $Order = null;
            if ($event->hasArgument('Order')) {
                $Order = $event->getArgument('Order');
            } elseif ($event->hasArgument('TargetOrder')) {
                $Order = $event->getArgument('TargetOrder');
            }
            if (!$Order) {
                return;
            }

            $status = $Order->getOrderStatus();

            $this->postbackClient->send('order_status', [
                'order_no' => $Order->getOrderNo(),
                'site_code' => $this->config->siteCode(),
                'order_status_id' => $status ? $status->getId() : null,
                'order_status_name' => $status ? $status->getName() : null,
            ]);
        } catch (\Throwable $e) {
            // 送信失敗はPostbackClient内でログ済み。管理操作を止めないための保険
        }
    }
}
