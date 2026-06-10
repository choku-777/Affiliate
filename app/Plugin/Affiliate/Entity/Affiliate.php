<?php

namespace Plugin\Affiliate\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Customer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * アフィリエイター。
 *
 * @ORM\Table(name="plg_affiliate")
 * @ORM\Entity(repositoryClass="Plugin\Affiliate\Repository\AffiliateRepository")
 */
class Affiliate
{
    /** 申請中（未承認） */
    const STATUS_PENDING = 1;
    /** 承認済み（URL発行・成果計測の対象） */
    const STATUS_APPROVED = 2;
    /** 却下 */
    const STATUS_REJECTED = 3;
    /** 停止 */
    const STATUS_SUSPENDED = 4;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * アフィリエイトURLに付与する一意のコード。
     *
     * @ORM\Column(name="affiliate_code", type="string", length=64, unique=true)
     */
    private $affiliate_code;

    /**
     * @ORM\Column(name="bank_name", type="string", length=255, nullable=true)
     */
    private $bank_name;

    /**
     * @ORM\Column(name="bank_branch", type="string", length=255, nullable=true)
     */
    private $bank_branch;

    /**
     * 口座種別（普通／当座 など）。
     *
     * @ORM\Column(name="account_type", type="string", length=32, nullable=true)
     */
    private $account_type;

    /**
     * @ORM\Column(name="account_number", type="string", length=32, nullable=true)
     */
    private $account_number;

    /**
     * @ORM\Column(name="account_holder", type="string", length=255, nullable=true)
     */
    private $account_holder;

    /**
     * @ORM\Column(name="status", type="smallint", options={"default":1})
     */
    private $status = self::STATUS_PENDING;

    /**
     * 任意：EC-CUBE会員と紐付ける場合に使用。
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $Customer;

    /**
     * @ORM\Column(name="create_date", type="datetimetz")
     * @Gedmo\Timestampable(on="create")
     */
    private $create_date;

    /**
     * @ORM\Column(name="update_date", type="datetimetz")
     * @Gedmo\Timestampable(on="update")
     */
    private $update_date;

    /**
     * @ORM\Column(name="approved_date", type="datetimetz", nullable=true)
     */
    private $approved_date;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAffiliateCode()
    {
        return $this->affiliate_code;
    }

    public function setAffiliateCode($code): self
    {
        $this->affiliate_code = $code;

        return $this;
    }

    public function getBankName()
    {
        return $this->bank_name;
    }

    public function setBankName($bankName): self
    {
        $this->bank_name = $bankName;

        return $this;
    }

    public function getBankBranch()
    {
        return $this->bank_branch;
    }

    public function setBankBranch($bankBranch): self
    {
        $this->bank_branch = $bankBranch;

        return $this;
    }

    public function getAccountType()
    {
        return $this->account_type;
    }

    public function setAccountType($accountType): self
    {
        $this->account_type = $accountType;

        return $this;
    }

    public function getAccountNumber()
    {
        return $this->account_number;
    }

    public function setAccountNumber($accountNumber): self
    {
        $this->account_number = $accountNumber;

        return $this;
    }

    public function getAccountHolder()
    {
        return $this->account_holder;
    }

    public function setAccountHolder($accountHolder): self
    {
        $this->account_holder = $accountHolder;

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

    public function isApproved(): bool
    {
        return $this->getStatus() === self::STATUS_APPROVED;
    }

    public function getCustomer(): ?Customer
    {
        return $this->Customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->Customer = $customer;

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

    public function getUpdateDate()
    {
        return $this->update_date;
    }

    public function setUpdateDate($updateDate): self
    {
        $this->update_date = $updateDate;

        return $this;
    }

    public function getApprovedDate()
    {
        return $this->approved_date;
    }

    public function setApprovedDate($approvedDate): self
    {
        $this->approved_date = $approvedDate;

        return $this;
    }
}
