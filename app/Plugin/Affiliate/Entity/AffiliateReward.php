<?php

namespace Plugin\Affiliate\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Order;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 成果（1注文＝1レコード）。注文発生で pending として作成し、
 * 返品・キャンセル期間経過後にバッチで confirmed/cancelled に更新する。
 *
 * @ORM\Table(name="plg_affiliate_reward")
 * @ORM\Entity(repositoryClass="Plugin\Affiliate\Repository\AffiliateRewardRepository")
 */
class AffiliateReward
{
    /** 未確定（返品・キャンセル期間中） */
    const STATUS_PENDING = 1;
    /** 確定（支払い対象） */
    const STATUS_CONFIRMED = 2;
    /** 取消（注文キャンセル・返品） */
    const STATUS_CANCELLED = 3;
    /** 支払済 */
    const STATUS_PAID = 4;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Plugin\Affiliate\Entity\Affiliate")
     * @ORM\JoinColumn(name="affiliate_id", referencedColumnName="id", nullable=false)
     */
    private $Affiliate;

    /**
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $Order;

    /**
     * 報酬計算の対象金額（注文合計＝送料・税込）。
     *
     * @ORM\Column(name="order_total", type="decimal", precision=12, scale=2, options={"default":0})
     */
    private $order_total = 0;

    /**
     * 適用した料率（%）。設定変更後も当時の料率を保持する。
     *
     * @ORM\Column(name="rate_applied", type="decimal", precision=5, scale=2, options={"default":0})
     */
    private $rate_applied = 0;

    /**
     * 報酬額（円）。
     *
     * @ORM\Column(name="reward_amount", type="decimal", precision=12, scale=2, options={"default":0})
     */
    private $reward_amount = 0;

    /**
     * @ORM\Column(name="status", type="smallint", options={"default":1})
     */
    private $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(name="converted_date", type="datetimetz")
     */
    private $converted_date;

    /**
     * @ORM\Column(name="confirmed_date", type="datetimetz", nullable=true)
     */
    private $confirmed_date;

    /**
     * @ORM\Column(name="paid_date", type="datetimetz", nullable=true)
     */
    private $paid_date;

    /**
     * @ORM\ManyToOne(targetEntity="Plugin\Affiliate\Entity\AffiliatePayment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $Payment;

    public function getId()
    {
        return $this->id;
    }

    public function getAffiliate(): ?Affiliate
    {
        return $this->Affiliate;
    }

    public function setAffiliate(?Affiliate $affiliate): self
    {
        $this->Affiliate = $affiliate;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->Order;
    }

    public function setOrder(?Order $order): self
    {
        $this->Order = $order;

        return $this;
    }

    public function getOrderTotal()
    {
        return $this->order_total;
    }

    public function setOrderTotal($orderTotal): self
    {
        $this->order_total = $orderTotal;

        return $this;
    }

    public function getRateApplied()
    {
        return $this->rate_applied;
    }

    public function setRateApplied($rateApplied): self
    {
        $this->rate_applied = $rateApplied;

        return $this;
    }

    public function getRewardAmount()
    {
        return $this->reward_amount;
    }

    public function setRewardAmount($rewardAmount): self
    {
        $this->reward_amount = $rewardAmount;

        return $this;
    }

    public function getStatus(): int
    {
        return (int) $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getConvertedDate()
    {
        return $this->converted_date;
    }

    public function setConvertedDate($convertedDate): self
    {
        $this->converted_date = $convertedDate;

        return $this;
    }

    public function getConfirmedDate()
    {
        return $this->confirmed_date;
    }

    public function setConfirmedDate($confirmedDate): self
    {
        $this->confirmed_date = $confirmedDate;

        return $this;
    }

    public function getPaidDate()
    {
        return $this->paid_date;
    }

    public function setPaidDate($paidDate): self
    {
        $this->paid_date = $paidDate;

        return $this;
    }

    public function getPayment(): ?AffiliatePayment
    {
        return $this->Payment;
    }

    public function setPayment(?AffiliatePayment $payment): self
    {
        $this->Payment = $payment;

        return $this;
    }
}
