<?php

namespace App\Console\Commands;

use App\Models\CurrencyRate;
use App\Services\CurrencyRateService;
use Illuminate\Console\Command;

class FetchCurrencyRates extends Command
{
    protected $signature = 'currency:fetch-rates';

    protected $description = 'Fetch latest currency exchange rates';

    public function handle(CurrencyRateService $service): int
    {
        $rates = $service->fetchRates();

        if (empty($rates)) {
            $this->warn('Could not fetch rates from API. Using cached or default rates.');

            return Command::FAILURE;
        }

        $now = now();
        foreach ($rates as $currency => $rate) {
            CurrencyRate::updateOrCreate(
                ['currency' => $currency],
                ['rate' => $rate, 'updated_at' => $now]
            );
        }

        $this->info('Successfully fetched and stored '.count($rates).' currency rates.');

        return Command::SUCCESS;
    }
}
