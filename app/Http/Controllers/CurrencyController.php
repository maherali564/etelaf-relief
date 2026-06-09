<?php

namespace App\Http\Controllers;

use App\Models\CurrencyRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{
    public function rates(): JsonResponse
    {
        $rates = Cache::remember('currency_rates', 3600, function () {
            return CurrencyRate::all()->pluck('rate', 'currency');
        });
        $rates['USD'] = 1;

        return response()->json($rates);
    }
}
