<?php

namespace Plugin\Affiliate\EventListener;

use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\Affiliate\Service\PostbackClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * 管理画面で注文が編集（ステータス変更等）された際、注文番号と現在のステータスを
 * 管理サービスへ送信する。キャンセル・返品時の成果取消を管理サービス側に反映するため。
 */
class AdminOrderStatusListener implements EventSubscriberInterface
{
    private $postbackClient;

    public function __construct(PostbackClient $postbackClient)
    {
        $this->postbackClient = $postbackClient;
    }

    public static function getSubscribedEvents()
    {
        return [
            EccubeEvents::ADMIN_ORDER_EDIT_INDEX_COMPLETE => 'onOrderEditComplete',
        ];
    }

    public function onOrderEditComplete(EventArgs $event)
    {
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
            'order_status_id' => $status ? $status->getId() : null,
            'order_status_name' => $status ? $status->getName() : null,
        ]);
    }
}
