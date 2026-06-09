<?php

namespace App\Mail;

use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterVerification extends Mailable
{
    use Queueable, SerializesModels;

    public Newsletter $subscriber;

    public function __construct(Newsletter $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('common.newsletter_verify_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter.verify',
            with: ['subscriber' => $this->subscriber],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
