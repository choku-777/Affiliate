<?php

namespace Plugin\Affiliate\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * アフィリエイトプラグインの設定（単一レコード）。
 *
 * @ORM\Table(name="plg_affiliate_config")
 * @ORM\Entity(repositoryClass="Plugin\Affiliate\Repository\AffiliateConfigRepository")
 */
class AffiliateConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * 報酬料率（%）。
     *
     * @ORM\Column(name="commission_rate", type="decimal", precision=5, scale=2, options={"default":5})
     */
    private $commission_rate = '5.00';

    /**
     * 成果紐付けクッキーの有効期間（日）。
     *
     * @ORM\Column(name="cookie_lifetime_days", type="integer", options={"default":30})
     */
    private $cookie_lifetime_days = 30;

    /**
     * 注文発生から報酬確定までの猶予期間（日）。
     *
     * @ORM\Column(name="confirm_after_days", type="integer", options={"default":30})
     */
    private $confirm_after_days = 30;

    /**
     * 最低支払額（円）。
     *
     * @ORM\Column(name="min_payout_amount", type="integer", options={"default":5000})
     */
    private $min_payout_amount = 5000;

    public function getId()
    {
        return $this->id;
    }

    public function getCommissionRate()
    {
        return $this->commission_rate;
    }

    public function setCommissionRate($commissionRate): self
    {
        $this->commission_rate = $commissionRate;

        return $this;
    }

    public function getCookieLifetimeDays(): int
    {
        return (int) $this->cookie_lifetime_days;
    }

    public function setCookieLifetimeDays(int $days): self
    {
        $this->cookie_lifetime_days = $days;

        return $this;
    }

    public function getConfirmAfterDays(): int
    {
        return (int) $this->confirm_after_days;
    }

    public function setConfirmAfterDays(int $days): self
    {
        $this->confirm_after_days = $days;

        return $this;
    }

    public function getMinPayoutAmount(): int
    {
        return (int) $this->min_payout_amount;
    }

    public function setMinPayoutAmount(int $amount): self
    {
        $this->min_payout_amount = $amount;

        return $this;
    }
}
