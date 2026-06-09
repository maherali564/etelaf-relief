<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IdempotencyHelper
{
    public static function generateKey(string $prefix = ''): string
    {
        return $prefix.'_'.Str::random(32);
    }

    public static function checkAndMark(string $idempotencyKey, string $modelClass, string $idField = 'idempotency_key'): bool
    {
        if (empty($idempotencyKey) || empty($modelClass)) {
            return false;
        }

        $instance = new $modelClass;
        $table = $instance->getTable();

        try {
            DB::table($table)->insertOrIgnore([$idField => $idempotencyKey]);

            $exists = DB::table($table)->where($idField, $idempotencyKey)->exists();

            return ! $exists;
        } catch (\Exception $e) {
            Log::warning('IdempotencyHelper: operation failed', [
                'idempotency_key' => $idempotencyKey,
                'error' => $e->getMessage(),
            ]);

            return true;
        }
    }
}
