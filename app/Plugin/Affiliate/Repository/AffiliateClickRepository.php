<?php

namespace Plugin\Affiliate\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Eccube\Repository\AbstractRepository;
use Plugin\Affiliate\Entity\AffiliateClick;

class AffiliateClickRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffiliateClick::class);
    }
}
