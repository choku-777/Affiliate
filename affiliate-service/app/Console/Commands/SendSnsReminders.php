<?php

namespace App\Console\Commands;

use App\Mail\SnsReminderMail;
use App\Models\Affiliate;
use App\Models\SampleRequest;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * SNS投稿の自動リマインド（日次）。
 * 発送済み・投稿に同意済み・有効な申告がない申込に、今の段階のメールを1通だけ送る。
 * 段階: お届けから○日後（使用感の確認）→ 期限の○日前 → 期限の翌日（最後）。同じ段階は二度送らない。
 * 到着日が未確定（配達中）の申込は対象外（到着日は affiliate:check-deliveries で記録する）。
 * 初回実行時に過去の段階をまとめて送らないよう、「今いる段階」だけを判定する。
 */
class SendSnsReminders extends Command
{
    protected $signature = 'affiliate:sns-reminders {--dry-run : 送信せず対象だけ表示する}';

    protected $description = 'SNS投稿の申告がないサンプル受取者へリマインドメールを送る';

    public function handle(): int
    {
        $setting = Setting::current();
        if (!$setting->sns_reminder_enabled) {
            $this->info('リマインドは設定でOFFになっています。');

            return self::SUCCESS;
        }

        $days = (int) $setting->sns_post_deadline_days;
        $afterDays = (int) $setting->sns_reminder_after_days;
        $beforeDays = (int) $setting->sns_reminder_before_days;
        $dry = (bool) $this->option('dry-run');
        $now = now();
        $sent = 0;

        $targets = SampleRequest::with(['affiliate', 'snsPosts'])
            ->where('status', SampleRequest::STATUS_SHIPPED)
            ->whereNotNull('sns_consent_at')
            ->whereNotNull('delivered_at')
            ->get();

        foreach ($targets as $request) {
            $affiliate = $request->affiliate;
            if (!$affiliate || $affiliate->status !== Affiliate::STATUS_APPROVED || !$affiliate->email) {
                continue;
            }
            if ($request->hasActiveSnsPost()) {
                continue;
            }

            $deadline = $request->snsDeadline($days);
            [$type, $column] = match (true) {
                $now->greaterThan($deadline) => [SnsReminderMail::TYPE_OVERDUE, 'reminder_overdue_at'],
                $now->greaterThanOrEqualTo($deadline->copy()->startOfDay()->subDays($beforeDays)) => [SnsReminderMail::TYPE_BEFORE, 'reminder_before_at'],
                $now->greaterThanOrEqualTo($request->delivered_at->copy()->startOfDay()->addDays($afterDays)) => [SnsReminderMail::TYPE_ARRIVAL, 'reminder_arrival_at'],
                default => [null, null],
            };
            if (!$type || $request->{$column}) {
                continue;
            }

            $this->line(sprintf('%s %s（期限 %s）→ %s', $dry ? '[対象]' : '[送信]', $affiliate->name, $deadline->format('Y-m-d'), $type));
            if ($dry) {
                continue;
            }

            try {
                Mail::to($affiliate->email)->send(new SnsReminderMail($affiliate, $type, $request));
                $request->update([$column => now()]);
                $sent++;
            } catch (\Throwable $e) {
                Log::error('SNSリマインドの送信に失敗しました: '.$e->getMessage(), ['sample_request_id' => $request->id]);
            }
        }

        $this->info($dry ? '確認のみ（送信していません）' : "{$sent}件送信しました。");

        return self::SUCCESS;
    }
}
