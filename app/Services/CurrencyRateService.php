<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyRateService
{
    protected array $defaultRates = [
        'USD' => 1.0,
        'EUR' => 0.92,
        'GBP' => 0.79,
        'SAR' => 3.75,
        'AED' => 3.67,
        'QAR' => 3.64,
    ];

    /**
     * Fetch latest currency rates from exchangerate-api.com
     *
     * @return array Rates keyed by currency code, empty on failure
     */
    public function fetchRates(): array
    {
        try {
            $response = Http::timeout(10)->get('https://api.exchangerate-api.com/v4/latest/USD');
            if ($response->successful()) {
                $rates = $response->json('rates');
                Cache::put('currency_rates', $rates, now()->addHours(6));

                return $rates;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch currency rates', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Get exchange rate for a currency relative to USD
     *
     * @param  string  $currency  The currency code (e.g., 'EUR')
     * @param  float|null  $default  Fallback rate if API unavailable
     * @return float The exchange rate
     */
    public function getRate(string $currency, ?float $default = null): float
    {
        $rates = Cache::get('currency_rates', []);
        if (empty($rates)) {
            $rates = $this->fetchRates();
        }

        if ($currency === 'USD') {
            return 1.0;
        }

        return $rates[$currency] ?? $default ?? $this->defaultRates[$currency] ?? 1.0;
    }

    /**
     * Convert an amount between two currencies
     *
     * @param  float  $amount  The amount to convert
     * @param  string  $from  Source currency code
     * @param  string  $to  Target currency code
     * @return float Converted amount
     */
    public function convert(float $amount, string $from, string $to): float
    {
        $fromRate = $this->getRate($from);
        $toRate = $this->getRate($to);
        if ($fromRate <= 0 || $toRate <= 0) {
            return $amount;
        }

        return round($amount * ($toRate / $fromRate), 2);
    }
}
