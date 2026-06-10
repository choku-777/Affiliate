<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * アフィリエイター承認通知メール。
 */
class AffiliateApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Affiliate $affiliate)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'アフィリエイト登録が承認されました',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-approved',
            with: [
                'affiliate' => $this->affiliate,
                'affiliateUrl' => $this->affiliate->affiliateUrl(),
                'mypageUrl' => $this->affiliate->mypageUrl(),
            ],
        );
    }
}
