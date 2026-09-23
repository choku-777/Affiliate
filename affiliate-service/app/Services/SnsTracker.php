<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\SampleRequest;
use App\Models\Setting;
use App\Models\SnsPost;
use Illuminate\Support\Collection;

/**
 * サンプル受取者ごとのSNS投稿の状況をまとめる（1行＝1人）。
 * 対象：新方式で発送済みの人、仕組み導入前にサンプルを送った人、投稿の申告がある人。
 *
 * stage（段階）:
 *   pending=確認中（#PR確認待ち） / checked=確認済み / approved=掲載中 / rejected=却下・非表示
 *   unposted=未申告 / overdue=期限切れ / legacy=導入前の方（未申告・期限なし）
 */
class SnsTracker
{
    const TABS = [
        'action' => '要対応',
        'waiting' => '申告待ち',
        'done' => '確認済み・掲載中',
        'rejected' => '却下・非表示',
        'legacy' => '導入前の方',
        'all' => 'すべて',
    ];

    public function rows(): Collection
    {
        $days = (int) Setting::current()->sns_post_deadline_days;

        $affiliates = Affiliate::with([
            'snsPosts',
            'sampleRequests' => fn ($q) => $q->where('status', SampleRequest::STATUS_SHIPPED),
        ])
            ->where(function ($q) {
                $q->whereNotNull('sample_sent_at')
                    ->orWhereHas('sampleRequests', fn ($s) => $s->where('status', SampleRequest::STATUS_SHIPPED))
                    ->orWhereHas('snsPosts');
            })
            ->get();

        // 停止・却下した方は、投稿の記録がない限り対象外（フォローの必要がないため）
        $affiliates = $affiliates->filter(
            fn ($a) => $a->status === Affiliate::STATUS_APPROVED || $a->snsPosts->isNotEmpty()
        );

        return $affiliates->map(function (Affiliate $affiliate) use ($days) {
            $request = $affiliate->sampleRequests->first(); // 新方式の発送（新しい順の先頭）
            $legacy = !$request && $affiliate->sample_sent_at !== null;
            $deadline = $request?->snsDeadline($days);
            $posts = $affiliate->snsPosts; // 新しい順
            $active = $posts->first(fn ($p) => in_array($p->status, [
                SnsPost::STATUS_PENDING, SnsPost::STATUS_CHECKED, SnsPost::STATUS_APPROVED,
            ], true));
            $latest = $active ?? $posts->first();

            $stage = match (true) {
                $active?->status === SnsPost::STATUS_PENDING => 'pending',
                $active?->status === SnsPost::STATUS_CHECKED => 'checked',
                $active?->status === SnsPost::STATUS_APPROVED => 'approved',
                $latest !== null => 'rejected',
                $legacy => 'legacy',
                $deadline && now()->greaterThan($deadline) => 'overdue',
                default => 'unposted',
            };

            return (object) [
                'affiliate' => $affiliate,
                'sampleRequest' => $request,
                'legacy' => $legacy,
                'shippedAt' => $request?->shipped_at ?? $affiliate->sample_sent_at,
                'deliveredAt' => $request?->delivered_at,
                'inTransit' => $request && !$request->delivered_at,
                'deadline' => $deadline,
                'daysLeft' => $deadline ? (int) now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false) : null,
                'latestPost' => $latest,
                'postCount' => $posts->count(),
                'stage' => $stage,
            ];
        })->values();
    }

    /**
     * タブごとに絞り込み、並べ替える。
     */
    public function filter(Collection $rows, string $tab): Collection
    {
        $filtered = match ($tab) {
            'action' => $rows->whereIn('stage', ['pending', 'overdue']),
            'waiting' => $rows->whereIn('stage', ['unposted', 'overdue']),
            'done' => $rows->whereIn('stage', ['checked', 'approved']),
            'rejected' => $rows->where('stage', 'rejected'),
            'legacy' => $rows->where('stage', 'legacy'),
            default => $rows,
        };

        return $filtered->sortBy(function ($r) {
            // 確認中を最優先 → 期限の近い順 → 期限なし（導入前の方）は送付日順
            $priority = $r->stage === 'pending' ? 0 : 1;
            $key = $r->deadline?->timestamp ?? (PHP_INT_MAX - ($r->shippedAt?->timestamp ?? 0));

            return sprintf('%d-%020d', $priority, max(0, $key));
        })->values();
    }

    public function counts(Collection $rows): array
    {
        $counts = [];
        foreach (array_keys(self::TABS) as $tab) {
            $counts[$tab] = $this->filter($rows, $tab)->count();
        }

        return $counts;
    }
}
