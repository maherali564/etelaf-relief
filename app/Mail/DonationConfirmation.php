<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Donation $donation;

    public string $type;

    public function __construct(Donation $donation, string $type = 'completed')
    {
        $this->donation = $donation;
        $this->type = $type;
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'instant' => 'تم التبرع بنجاح - '.config('app.name'),
            'under_review' => 'تم استلام طلب التبرع - '.config('app.name'),
            'completed' => 'تم تأكيد التبرع - '.config('app.name'),
            'failed' => 'تعذر إتمام التبرع - '.config('app.name'),
        ];

        return new Envelope(
            subject: $subjects[$this->type] ?? 'إشعار تبرع - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.donation',
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
