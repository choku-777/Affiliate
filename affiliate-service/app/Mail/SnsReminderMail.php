<?php

namespace App\Mail;

use App\Models\Affiliate;
use App\Models\SampleRequest;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * SNS投稿のリマインド／お願いメール。
 * type: arrival（お届け後の使用感の確認）/ before（期限前）/ overdue（期限切れ）/ manual（手動催促）/ legacy（仕組み導入前の送付者へのお願い）
 */
class SnsReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    const TYPE_ARRIVAL = 'arrival';
    const TYPE_BEFORE = 'before';
    const TYPE_OVERDUE = 'overdue';
    const TYPE_MANUAL = 'manual';
    const TYPE_LEGACY = 'legacy';

    public Setting $setting;
    public ?Carbon $deadline;

    public function __construct(
        public Affiliate $affiliate,
        public string $type,
        public ?SampleRequest $sampleRequest = null,
    ) {
        $this->setting = Setting::current();
        $this->deadline = $sampleRequest?->snsDeadline((int) $this->setting->sns_post_deadline_days);
    }

    public function envelope(): Envelope
    {
        $deadline = $this->deadline?->format('n月j日');

        $subject = match ($this->type) {
            self::TYPE_ARRIVAL => 'サンプルはいかがでしたか？／ご感想投稿のお願い',
            self::TYPE_BEFORE => 'SNS投稿の期限が近づいています'.($deadline ? "（{$deadline}まで）" : ''),
            self::TYPE_OVERDUE => 'SNS投稿の期限を過ぎています',
            self::TYPE_LEGACY => 'サンプルのご感想をお聞かせください',
            default => 'ご感想投稿のお願い',
        };

        return new Envelope(subject: '【うましっぽ】'.$subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.sns-reminder');
    }
}
