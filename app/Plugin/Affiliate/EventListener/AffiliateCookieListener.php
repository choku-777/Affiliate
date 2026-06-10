<?php

namespace Plugin\Affiliate\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Plugin\Affiliate\Entity\AffiliateClick;
use Plugin\Affiliate\Repository\AffiliateConfigRepository;
use Plugin\Affiliate\Repository\AffiliateRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * 任意のページに付与された ?affiliate=CODE を検知し、成果紐付け用クッキーを発行する。
 * 同時にクリックログを記録する。複数経由時はラストクリックで上書きされる。
 */
class AffiliateCookieListener implements EventSubscriberInterface
{
    /** クエリパラメータ名 */
    const QUERY_KEY = 'affiliate';
    /** クッキー名 */
    const COOKIE_NAME = 'affiliate_code';

    private $affiliateRepository;
    private $configRepository;
    private $entityManager;

    public function __construct(
        AffiliateRepository $affiliateRepository,
        AffiliateConfigRepository $configRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->affiliateRepository = $affiliateRepository;
        $this->configRepository = $configRepository;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            // 低優先度で動かし、本来のレスポンス生成を妨げない
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
        if (!$code) {
            return;
        }

        $affiliate = $this->affiliateRepository->findApprovedByCode($code);
        if (!$affiliate) {
            return;
        }

        $config = $this->configRepository->get();
        $expire = new \DateTime();
        $expire->modify('+'.$config->getCookieLifetimeDays().' day');

        $cookie = Cookie::create(
            self::COOKIE_NAME,
            $code,
            $expire,
            '/',
            null,
            $request->isSecure(),
            true,          // httpOnly（サーバ側でのみ参照するため）
            false,
            Cookie::SAMESITE_LAX
        );
        $event->getResponse()->headers->setCookie($cookie);

        // クリックログ
        $click = new AffiliateClick();
        $click->setAffiliate($affiliate)
            ->setIp($request->getClientIp())
            ->setReferer($request->headers->get('referer'))
            ->setLandingUrl($request->getPathInfo());
        $this->entityManager->persist($click);
        $this->entityManager->flush();
    }

    /**
     * Symfony のバージョン差異を吸収してメインリクエスト判定を行う。
     */
    private function isMainRequest(ResponseEvent $event): bool
    {
        if (method_exists($event, 'isMainRequest')) {
            return $event->isMainRequest();
        }

        return $event->isMasterRequest();
    }
}
