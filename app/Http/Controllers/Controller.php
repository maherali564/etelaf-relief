<?php

namespace App\Http\Controllers;

use App\Models\Donation;

abstract class Controller
{
    protected function verifyAccessToken(Donation $donation): void
    {
        if (empty($donation->idempotency_key)) {
            return;
        }

        $submittedToken = request('token');

        if (! $submittedToken || ! hash_equals($donation->idempotency_key, $submittedToken)) {
            abort(403, 'Invalid access token');
        }
    }
}
