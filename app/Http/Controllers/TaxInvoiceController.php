<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Barryvdh\DomPDF\Facade\Pdf;

class TaxInvoiceController extends Controller
{
    public function download(string $locale, Donation $donation)
    {
        if (auth()->guest()) {
            $this->verifyAccessToken($donation);
        }

        if ($donation->status !== 'completed') {
            abort(404);
        }

        $pdf = Pdf::loadView('pdf.tax-invoice', compact('donation'));
        $filename = 'tax-invoice-'.$donation->id.'.pdf';

        return $pdf->download($filename);
    }
}
