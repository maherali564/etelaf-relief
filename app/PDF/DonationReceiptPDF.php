<?php

namespace App\PDF;

use App\Models\Donation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DonationReceiptPDF
{
    public function generate(Donation $donation): string
    {
        $data = [
            'donor_name' => $donation->is_anonymous ? 'متبرع مجهول' : $donation->donor_name,
            'amount' => number_format($donation->amount, 2),
            'currency' => $donation->currency,
            'date' => $donation->donated_at?->format('Y-m-d') ?? $donation->created_at->format('Y-m-d'),
            'transaction_id' => $donation->transaction_id,
            'status' => $donation->status,
            'project' => $donation->project?->title ? trans_field($donation->project, 'title') : null,
            'campaign' => $donation->campaign?->title ? trans_field($donation->campaign, 'title') : null,
            'story' => $donation->story?->title ? trans_field($donation->story, 'title') : null,
            'payment_method' => $donation->paymentMethod?->name,
            'site_name' => config('app.name'),
        ];

        $pdf = Pdf::loadView('pdfs.receipt', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'receipt-'.$donation->id.'.pdf';
        $path = 'receipts/'.$filename;

        Storage::disk('local')->put($path, $pdf->output());

        return storage_path('app/'.$path);
    }

    public function stream(Donation $donation)
    {
        $data = [
            'donor_name' => $donation->is_anonymous ? 'متبرع مجهول' : $donation->donor_name,
            'amount' => number_format($donation->amount, 2),
            'currency' => $donation->currency,
            'date' => $donation->donated_at?->format('Y-m-d') ?? $donation->created_at->format('Y-m-d'),
            'transaction_id' => $donation->transaction_id,
            'status' => $donation->status,
            'project' => $donation->project?->title ? trans_field($donation->project, 'title') : null,
            'campaign' => $donation->campaign?->title ? trans_field($donation->campaign, 'title') : null,
            'story' => $donation->story?->title ? trans_field($donation->story, 'title') : null,
            'payment_method' => $donation->paymentMethod?->name,
            'site_name' => config('app.name'),
        ];

        $pdf = Pdf::loadView('pdfs.receipt', $data);

        return $pdf->stream('receipt-'.$donation->id.'.pdf');
    }
}
