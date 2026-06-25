<?php

namespace App\Services\Payment;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IdempotencyHelper
{
    public static function generateKey(string $prefix = ''): string
    {
        return $prefix.'_'.Str::random(32);
    }

    public static function checkAndMark(string $idempotencyKey): bool
    {
        if (empty($idempotencyKey)) {
            return false;
        }

        if (DB::table('idempotency_keys')->where('key', $idempotencyKey)->exists()) {
            Log::info('IdempotencyHelper: duplicate detected', ['idempotency_key' => $idempotencyKey]);
            return true;
        }

        try {
            DB::table('idempotency_keys')->insert(['key' => $idempotencyKey]);
        } catch (QueryException) {
            Log::info('IdempotencyHelper: duplicate detected (race)', ['idempotency_key' => $idempotencyKey]);
            return true;
        }

        return false;
    }
}
