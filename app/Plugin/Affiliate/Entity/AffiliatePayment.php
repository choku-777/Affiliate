<?php

namespace Plugin\Affiliate\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 支払いバッチ（締め単位の支払いまとめ）。実際の振込は手動で行い、状態を更新する。
 *
 * @ORM\Table(name="plg_affiliate_payment")
 * @ORM\Entity(repositoryClass="Plugin\Affiliate\Repository\AffiliatePaymentRepository")
 */
class AffiliatePayment
{
    /** 支払い予定 */
    const STATUS_SCHEDULED = 1;
    /** 支払済 */
    const STATUS_PAID = 2;

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
     * 締め期間ラベル（例: 2026-05）。
     *
     * @ORM\Column(name="period", type="string", length=32, nullable=true)
     */
    private $period;

    /**
     * @ORM\Column(name="total_amount", type="decimal", precision=12, scale=2, options={"default":0})
     */
    private $total_amount = 0;

    /**
     * @ORM\Column(name="status", type="smallint", options={"default":1})
     */
    private $status = self::STATUS_SCHEDULED;

    /**
     * @ORM\Column(name="paid_date", type="datetimetz", nullable=true)
     */
    private $paid_date;

    /**
     * @ORM\Column(name="create_date", type="datetimetz")
     * @Gedmo\Timestampable(on="create")
     */
    private $create_date;

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

    public function getPeriod()
    {
        return $this->period;
    }

    public function setPeriod($period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    public function setTotalAmount($totalAmount): self
    {
        $this->total_amount = $totalAmount;

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

    public function getPaidDate()
    {
        return $this->paid_date;
    }

    public function setPaidDate($paidDate): self
    {
        $this->paid_date = $paidDate;

        return $this;
    }

    public function getCreateDate()
    {
        return $this->create_date;
    }

    public function setCreateDate($createDate): self
    {
        $this->create_date = $createDate;

        return $this;
    }
}
