<?php

use App\Services\CurrencyRateService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::forget('currency_rates');
    Cache::put('currency_rates', ['EUR' => 0.92, 'GBP' => 0.79, 'SAR' => 3.75], 3600);
});

it('returns 1.0 for USD rate', function () {
    $service = new CurrencyRateService;

    expect($service->getRate('USD'))->toBe(1.0);
});

it('returns cached rate for EUR', function () {
    $service = new CurrencyRateService;

    expect($service->getRate('EUR'))->toBe(0.92);
});

it('falls back to default rate when cache and API unavailable', function () {
    Cache::forget('currency_rates');
    $service = new CurrencyRateService;

    $rate = $service->getRate('SAR');
    expect($rate)->toBe(3.75);
});

it('falls back to provided default when no rate found', function () {
    Cache::forget('currency_rates');
    $service = new CurrencyRateService;

    $rate = $service->getRate('XYZ', 2.5);
    expect($rate)->toBe(2.5);
});

it('converts between currencies', function () {
    $service = new CurrencyRateService;

    $converted = $service->convert(100, 'USD', 'EUR');
    expect($converted)->toBe(92.0);
});

it('returns same amount when converting same currency', function () {
    $service = new CurrencyRateService;

    expect($service->convert(50, 'USD', 'USD'))->toBe(50.0);
});

it('handles zero rates gracefully', function () {
    Cache::put('currency_rates', ['XYZ' => 0], 3600);
    $service = new CurrencyRateService;

    expect($service->convert(100, 'USD', 'XYZ'))->toBe(100.0);
});
