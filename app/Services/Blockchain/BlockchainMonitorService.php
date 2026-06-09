<?php

namespace App\Services\Blockchain;

use App\Models\CryptoNetwork;
use Illuminate\Support\Facades\Log;

class BlockchainMonitorService
{
    public function checkAll(): void
    {
        $networks = CryptoNetwork::with('cryptocurrency')->active()->get();

        foreach ($networks as $network) {
            try {
                $this->checkNetwork($network);
            } catch (\Exception $e) {
                Log::error('Blockchain check failed for network', [
                    'network_id' => $network->id,
                    'network' => $network->network_name,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function checkNetwork(CryptoNetwork $network): void
    {
        $name = strtolower($network->network_name);

        if (str_contains($name, 'trc20') || str_contains($name, 'tron') || str_contains($name, 'trc')) {
            app(TrongridChecker::class)->check($network);

            return;
        }

        if (str_contains($name, 'erc20') || str_contains($name, 'ethereum') || str_contains($name, 'eth')) {
            app(EtherscanChecker::class)->check($network);

            return;
        }

        if (str_contains($name, 'bep20') || str_contains($name, 'bsc') || str_contains($name, 'binance')) {
            app(BscChecker::class)->check($network);

            return;
        }

        Log::warning('No blockchain checker available for network', [
            'network' => $network->network_name,
            'id' => $network->id,
        ]);
    }
}
