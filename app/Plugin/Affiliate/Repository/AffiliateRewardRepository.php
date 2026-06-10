<?php

namespace Plugin\Affiliate\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Eccube\Repository\AbstractRepository;
use Plugin\Affiliate\Entity\Affiliate;
use Plugin\Affiliate\Entity\AffiliateReward;

class AffiliateRewardRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffiliateReward::class);
    }

    /**
     * 注文に紐づく成果が既に存在するか（重複記録防止）。
     */
    public function existsByOrder($order): bool
    {
        return (bool) $this->findOneBy(['Order' => $order]);
    }

    /**
     * 確定対象の候補（未確定）を取得する。
     */
    public function findPending(): array
    {
        return $this->findBy(['status' => AffiliateReward::STATUS_PENDING]);
    }

    /**
     * 管理画面の検索用 QueryBuilder。
     *
     * @param array $searchData status, affiliate_id, converted_date_start/end を受け付ける
     */
    public function getQueryBuilderBySearchData(array $searchData)
    {
        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.Affiliate', 'a')
            ->addSelect('a')
            ->leftJoin('r.Order', 'o')
            ->addSelect('o');

        if (!empty($searchData['status'])) {
            $qb->andWhere('r.status = :status')
                ->setParameter('status', $searchData['status']);
        }

        if (!empty($searchData['affiliate_id'])) {
            $qb->andWhere('a.id = :affiliate_id')
                ->setParameter('affiliate_id', $searchData['affiliate_id']);
        }

        if (!empty($searchData['converted_date_start'])) {
            $qb->andWhere('r.converted_date >= :start')
                ->setParameter('start', $searchData['converted_date_start']);
        }

        if (!empty($searchData['converted_date_end'])) {
            $date = clone $searchData['converted_date_end'];
            $date->modify('+1 day');
            $qb->andWhere('r.converted_date < :end')
                ->setParameter('end', $date);
        }

        $qb->orderBy('r.id', 'DESC');

        return $qb;
    }

    /**
     * ステータスごとの報酬額合計を取得する（集計表示用）。
     *
     * @return array status をキーにした合計額の連想配列
     */
    public function getTotalsByStatus(array $searchData = []): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.status AS status, SUM(r.reward_amount) AS total')
            ->groupBy('r.status');

        if (!empty($searchData['affiliate_id'])) {
            $qb->innerJoin('r.Affiliate', 'a')
                ->andWhere('a.id = :affiliate_id')
                ->setParameter('affiliate_id', $searchData['affiliate_id']);
        }

        $result = [];
        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $result[(int) $row['status']] = $row['total'];
        }

        return $result;
    }

    /**
     * 指定アフィリエイターの確定済み（未払い）報酬を取得する。
     */
    public function findConfirmedUnpaid(Affiliate $affiliate): array
    {
        return $this->findBy([
            'Affiliate' => $affiliate,
            'status' => AffiliateReward::STATUS_CONFIRMED,
        ]);
    }
}
