<?php

namespace App\Mail;

use App\Models\SampleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * サンプル商品の発送完了をアンバサダー本人へ知らせるメール。
 * 伝票番号とヤマト運輸の追跡URLを添える。
 */
class SampleShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SampleRequest $sampleRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【うましっぽ】サンプル商品を発送しました');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.sample-shipped');
    }
}
