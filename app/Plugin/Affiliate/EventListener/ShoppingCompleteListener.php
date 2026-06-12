<?php

namespace Plugin\Affiliate\EventListener;

use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\Affiliate\Service\Config;
use Plugin\Affiliate\Service\PostbackClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * 注文完了時にクッキーを参照し、成果を管理サービスへ送信する。
 * 報酬計算・重複排除・承認判定は管理サービス側で行う。
 */
class ShoppingCompleteListener implements EventSubscriberInterface
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
            EccubeEvents::FRONT_SHOPPING_COMPLETE_INITIALIZE => 'onShoppingComplete',
        ];
    }

    public function onShoppingComplete(EventArgs $event)
    {
        if (!$event->hasArgument('Order')) {
            return;
        }
        $Order = $event->getArgument('Order');
        if (!$Order) {
            return;
        }

        $request = $event->getRequest();
        $code = $request->cookies->get(AffiliateCookieListener::COOKIE_NAME);
        if (!$code) {
            return;
        }

        $orderDate = $Order->getOrderDate() ?: $Order->getCreateDate();

        $this->postbackClient->send('conversion', [
            'affiliate_code' => $code,
            'site_code' => $this->config->siteCode(),
            'order_no' => $Order->getOrderNo(),
            'order_total' => (string) $Order->getPaymentTotal(),
            'order_date' => $orderDate ? $orderDate->format(\DateTime::ATOM) : null,
        ]);
    }
}
