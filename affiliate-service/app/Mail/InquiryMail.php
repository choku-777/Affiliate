<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * アフィリエイターからの問い合わせを管理者へ届けるメール。
 * 返信先(Reply-To)を本人のメールにして、管理者がそのまま返信できるようにする。
 */
class InquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Affiliate $affiliate,
        public string $subjectLine,
        public string $body,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【アフィリエイト問い合わせ】'.$this->subjectLine,
            replyTo: [new Address($this->affiliate->email, $this->affiliate->name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.inquiry');
    }
}
