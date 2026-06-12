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
 * 問い合わせを管理者へ届けるメール。
 * 登録前（未ログイン）・登録済み（ログイン）どちらの問い合わせにも対応する。
 * 返信先(Reply-To)を問い合わせ者のメールにして、管理者がそのまま返信できるようにする。
 */
class InquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $subjectLine,
        public string $body,
        public ?Affiliate $affiliate = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【アフィリエイト問い合わせ】'.$this->subjectLine,
            replyTo: [new Address($this->senderEmail, $this->senderName)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.inquiry');
    }
}
