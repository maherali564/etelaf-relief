<?php

namespace App\Services\Blockchain;

use App\Models\CryptoNetwork;
use App\Models\CryptoTransaction;
use App\Models\Donation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class TrongridChecker
{

    public function check(CryptoNetwork $network): void
    {
        $address = $network->wallet_address;
        if (! $address) {
            return;
        }

        $contract = $network->contract_address ?: Config::get('blockchain.trongrid.usdt_contract');
        $since = $network->last_checked_at?->timestamp ?? (now()->subDay()->timestamp * 1000);

        try {
            $response = Http::timeout(15)->get(Config::get('blockchain.trongrid.base_url').'/v1/accounts/'.$address.'/transactions/trc20', [
                'contract_address' => $contract,
                'min_timestamp' => $since * 1000,
                'limit' => 50,
                'only_confirmed' => 'true',
            ]);

            if (! $response->successful()) {
                Log::warning('Trongrid API error', [
                    'network' => $network->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return;
            }

            $data = $response->json('data', []);
            foreach ($data as $tx) {
                if (($tx['to'] ?? '') !== $address) {
                    continue;
                }
                if (($tx['type'] ?? '') !== 'Transfer') {
                    continue;
                }

                $txid = $tx['transaction_id'] ?? '';
                if (! $txid) {
                    continue;
                }

                $decimals = $tx['token_info']['decimals'] ?? 6;
                $value = $tx['value'] ?? '0';
                $amount = $value / (10 ** $decimals);

                $symbol = $tx['token_info']['symbol'] ?? 'USDT';

                if (CryptoTransaction::where('txid', $txid)->exists()) {
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
            Log::error('Trongrid check failed', [
                'network' => $network->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function matchAndConfirm(CryptoTransaction $cryptoTx, CryptoNetwork $network): void
    {
        $donation = Donation::where('status', 'pending')
            ->whereHas('paymentMethod.gateway', fn ($g) => $g->where('driver', 'crypto'))
            ->where(function ($q) use ($network) {
                $q->where('cryptocurrency_id', $network->cryptocurrency_id)
                    ->orWhereNull('cryptocurrency_id');
            })
            ->where('amount', '>=', $cryptoTx->amount * 0.99)
            ->where('amount', '<=', $cryptoTx->amount * 1.01)
            ->whereDoesntHave('confirmations')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($donation) {
            $cryptoTx->update([
                'matched_donation_id' => $donation->id,
                'status' => 'completed',
            ]);

            $donation->markCompleted();
            $donation->update([
                'cryptocurrency_id' => $network->cryptocurrency_id,
                'crypto_network_id' => $network->id,
            ]);

            Log::info('Crypto donation auto-confirmed', [
                'donation_id' => $donation->id,
                'txid' => $cryptoTx->txid,
                'amount' => $cryptoTx->amount,
            ]);
        } else {
            Log::info('Unmatched crypto transaction', [
                'txid' => $cryptoTx->txid,
                'amount' => $cryptoTx->amount,
                'network' => $network->network_name,
            ]);
        }
    }
}
