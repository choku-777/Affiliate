<?php

namespace Plugin\Affiliate\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * クリックログ（アフィリエイトURLでの来店記録）。
 *
 * @ORM\Table(name="plg_affiliate_click")
 * @ORM\Entity(repositoryClass="Plugin\Affiliate\Repository\AffiliateClickRepository")
 */
class AffiliateClick
{
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
     * @ORM\Column(name="ip", type="string", length=64, nullable=true)
     */
    private $ip;

    /**
     * @ORM\Column(name="referer", type="string", length=1024, nullable=true)
     */
    private $referer;

    /**
     * @ORM\Column(name="landing_url", type="string", length=1024, nullable=true)
     */
    private $landing_url;

    /**
     * @ORM\Column(name="click_date", type="datetimetz")
     * @Gedmo\Timestampable(on="create")
     */
    private $click_date;

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

    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getReferer()
    {
        return $this->referer;
    }

    public function setReferer($referer): self
    {
        $this->referer = $referer ? mb_substr($referer, 0, 1024) : null;

        return $this;
    }

    public function getLandingUrl()
    {
        return $this->landing_url;
    }

    public function setLandingUrl($landingUrl): self
    {
        $this->landing_url = $landingUrl ? mb_substr($landingUrl, 0, 1024) : null;

        return $this;
    }

    public function getClickDate()
    {
        return $this->click_date;
    }

    public function setClickDate($clickDate): self
    {
        $this->click_date = $clickDate;

        return $this;
    }
}
