<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public Donation $donation;

    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'إيصال تبرع - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.receipt',
        );
    }

    public function attachments(): array
    {
        $pdfPath = storage_path('app/receipts/receipt-'.$this->donation->id.'.pdf');
        if (file_exists($pdfPath)) {
            return [$pdfPath];
        }

        return [];
    }
}
