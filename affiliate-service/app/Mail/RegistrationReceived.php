<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * アフィリエイター登録の受付通知メール（申請直後に送信）。
 */
class RegistrationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Affiliate $affiliate)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'アフィリエイト登録を受け付けました',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-received',
            with: ['affiliate' => $this->affiliate],
        );
    }
}
