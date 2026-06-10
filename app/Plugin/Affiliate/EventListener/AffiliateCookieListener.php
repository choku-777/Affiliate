<?php

namespace Plugin\Affiliate\EventListener;

use Plugin\Affiliate\Service\PostbackClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * 任意のページに付与された ?affiliate=CODE を検知し、成果紐付け用クッキーを発行する。
 * コードの正当性チェックは管理サービス側（postback受信時）で行うため、ここではDB参照しない。
 */
class AffiliateCookieListener implements EventSubscriberInterface
{
    /** クエリパラメータ名 */
    const QUERY_KEY = 'affiliate';
    /** クッキー名 */
    const COOKIE_NAME = 'affiliate_code';

    private $cookieDays;
    private $trackClicks;
    private $postbackClient;

    public function __construct(int $cookieDays, bool $trackClicks, PostbackClient $postbackClient)
    {
        $this->cookieDays = $cookieDays > 0 ? $cookieDays : 30;
        $this->trackClicks = $trackClicks;
        $this->postbackClient = $postbackClient;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -10],
        ];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$this->isMainRequest($event)) {
            return;
        }

        $request = $event->getRequest();
        $code = $request->query->get(self::QUERY_KEY);
        if (!$code || !preg_match('/\A[A-Za-z0-9_-]{1,64}\z/', $code)) {
            return;
        }

        $expire = new \DateTime('+'.$this->cookieDays.' day');
        $cookie = Cookie::create(
            self::COOKIE_NAME,
            $code,
            $expire,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            Cookie::SAMESITE_LAX
        );
        $event->getResponse()->headers->setCookie($cookie);

        if ($this->trackClicks) {
            $this->postbackClient->send('click', [
                'affiliate_code' => $code,
                'ip' => $request->getClientIp(),
                'referer' => $request->headers->get('referer'),
                'landing_url' => $request->getUri(),
                'clicked_at' => (new \DateTime())->format(\DateTime::ATOM),
            ]);
        }
    }

    private function isMainRequest(ResponseEvent $event): bool
    {
        if (method_exists($event, 'isMainRequest')) {
            return $event->isMainRequest();
        }

        return $event->isMasterRequest();
    }
}
