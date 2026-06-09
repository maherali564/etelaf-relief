<?php

namespace App\Services;

use App\Models\Donation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CertificateService
{
    /**
     * Download a donation certificate as PDF
     *
     * @param  Donation  $donation  The completed donation
     * @return Response PDF download response
     */
    public function downloadCertificate(Donation $donation): Response
    {
        if ($donation->status !== 'completed') {
            abort(404);
        }

        $pdf = Pdf::loadView('pdf.certificate', compact('donation'));
        $filename = 'certificate-'.$donation->id.'.pdf';

        return $pdf->download($filename);
    }
}
