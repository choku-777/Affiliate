<?php

namespace Plugin\Affiliate\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Eccube\Repository\AbstractRepository;
use Plugin\Affiliate\Entity\Affiliate;

class AffiliateRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Affiliate::class);
    }

    /**
     * 承認済みアフィリエイターをコードで検索する。
     */
    public function findApprovedByCode(string $code): ?Affiliate
    {
        return $this->findOneBy([
            'affiliate_code' => $code,
            'status' => Affiliate::STATUS_APPROVED,
        ]);
    }

    /**
     * 管理画面の検索用 QueryBuilder。
     *
     * @param array $searchData status, name, email を受け付ける
     */
    public function getQueryBuilderBySearchData(array $searchData)
    {
        $qb = $this->createQueryBuilder('a');

        if (!empty($searchData['status'])) {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', $searchData['status']);
        }

        if (!empty($searchData['name'])) {
            $qb->andWhere('a.name LIKE :name')
                ->setParameter('name', '%'.$searchData['name'].'%');
        }

        if (!empty($searchData['email'])) {
            $qb->andWhere('a.email LIKE :email')
                ->setParameter('email', '%'.$searchData['email'].'%');
        }

        $qb->orderBy('a.id', 'DESC');

        return $qb;
    }
}
