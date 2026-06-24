<?php

namespace App\Services\Blockchain;

use App\Models\CryptoNetwork;
use App\Models\CryptoTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EtherscanChecker
{
    use BlockchainMatcher;

    const BASE_URL = 'https://api.etherscan.io/api';

    public function check(CryptoNetwork $network): void
    {
        $address = $network->wallet_address;
        if (! $address) {
            return;
        }

        $apiKey = config('services.etherscan.api_key', '');
        if (! $apiKey) {
            Log::warning('Etherscan API key not configured');

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

            if (! $response->successful() || $response->json('status') !== '1') {
                Log::warning('Etherscan API error', ['response' => $response->body()]);

                return;
            }

            $txs = $response->json('result', []);
            foreach ($txs as $tx) {
                if (strtolower($tx['to'] ?? '') !== strtolower($address)) {
                    continue;
                }

                $txid = $tx['hash'] ?? '';
                if (! $txid) {
                    continue;
                }

                if (CryptoTransaction::where('txid', $txid)->exists()) {
                    continue;
                }

                $decimals = $tx['tokenDecimal'] ?? 18;
                $value = $tx['value'] ?? '0';
                $amount = $value / (10 ** $decimals);
                $symbol = $tx['tokenSymbol'] ?? '';

                $time = $tx['timeStamp'] ?? 0;
                if ($time < $since) {
                    continue;
                }

                $cryptoTx = new CryptoTransaction();
                $cryptoTx->fill([
                    'txid' => $txid,
                    'from_address' => $tx['from'] ?? null,
                    'to_address' => $tx['to'] ?? null,
                    'amount' => $amount,
                    'currency' => $symbol,
                    'status' => 'pending',
                    'raw_data' => $tx,
                ]);
                $cryptoTx->crypto_network_id = $network->id;
                $cryptoTx->save();

                $this->matchAndConfirm($cryptoTx, $network);
            }

            $network->update(['last_checked_at' => now()]);

        } catch (\Exception $e) {
            Log::error('Etherscan check failed', [
                'network' => $network->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
