<?php

namespace App\Services\Blockchain;

use App\Models\CryptoNetwork;
use App\Models\CryptoTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BscChecker
{
    use BlockchainMatcher;

    const BASE_URL = 'https://api.bscscan.com/api';

    public function check(CryptoNetwork $network): void
    {
        $address = $network->wallet_address;
        if (! $address) {
            return;
        }

        $apiKey = config('services.bscscan.api_key', '');
        if (! $apiKey) {
            return;
        }

        $since = $network->last_checked_at?->timestamp ?? now()->subDay()->timestamp;

        try {
            $response = Http::timeout(15)->get(self::BASE_URL, [
                'module' => 'account',
                'action' => 'tokentx',
                'address' => $address,
                'startblock' => 0,
                'endblock' => 99999999,
                'sort' => 'desc',
                'apikey' => $apiKey,
            ]);

            if ($response->successful() && ($response->json('status') ?? '') === '1') {
                $this->processTransactions($response->json('result', []), $network, $since);
                $network->update(['last_checked_at' => now()]);
            }
        } catch (\Exception $e) {
            Log::error('BSC check failed', ['network' => $network->id, 'error' => $e->getMessage()]);
        }
    }

    protected function processTransactions(array $txs, CryptoNetwork $network, int $since): void
    {
        foreach ($txs as $tx) {
            if (strtolower($tx['to'] ?? '') !== strtolower($address = $network->wallet_address)) {
                continue;
            }
            $txid = $tx['hash'] ?? '';
            if (! $txid || CryptoTransaction::where('txid', $txid)->exists()) {
                continue;
            }

            $decimals = $tx['tokenDecimal'] ?? 18;
            $amount = ($tx['value'] ?? 0) / (10 ** $decimals);
            if (($tx['timeStamp'] ?? 0) < $since) {
                continue;
            }

            $cryptoTx = CryptoTransaction::create([
                'crypto_network_id' => $network->id,
                'txid' => $txid,
                'from_address' => $tx['from'] ?? null,
                'to_address' => $tx['to'] ?? null,
                'amount' => $amount,
                'currency' => $tx['tokenSymbol'] ?? '',
                'status' => 'pending',
                'raw_data' => $tx,
            ]);

            $this->matchAndConfirm($cryptoTx, $network);
        }
    }
}
