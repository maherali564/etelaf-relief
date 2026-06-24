<?php

namespace App\Http\Controllers;

use App\Models\Donation;

abstract class Controller
{
    protected function verifyAccessToken(Donation $donation): void
    {
        $submittedToken = request('token');

        if (empty($donation->idempotency_key) || ! $submittedToken || ! hash_equals($donation->idempotency_key, $submittedToken)) {
            abort(403, 'Invalid access token');
        }
    }
}
