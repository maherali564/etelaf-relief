<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\CertificateService;

class CertificateController extends Controller
{
    public function __construct(
        private readonly CertificateService $certificateService
    ) {}

    public function download(string $locale, Donation $donation)
    {
        if (auth()->guest()) {
            $this->verifyAccessToken($donation);
        }

        return $this->certificateService->downloadCertificate($donation);
    }
}
