<?php

namespace App\Mail;

use App\Models\SampleRequest;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * サンプル商品の発送完了をアンバサダー本人へ知らせるメール。
 * 伝票番号・追跡URLに加え、サンプルの条件であるSNS投稿のお願い（期限・ハッシュタグ・申告方法）を案内する。
 */
class SampleShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Setting $setting;
    public ?Carbon $deadline;

    public function __construct(public SampleRequest $sampleRequest)
    {
        $this->setting = Setting::current();
        $this->deadline = $sampleRequest->snsDeadline((int) $this->setting->sns_post_deadline_days);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【うましっぽ】サンプル商品を発送しました／SNS投稿のお願い');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.sample-shipped');
    }
}
