<?php

namespace App\Services\Blockchain;

use App\Models\CryptoNetwork;
use App\Models\CryptoTransaction;
use App\Models\Donation;
use Illuminate\Support\Facades\Log;

trait BlockchainMatcher
{
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
                'network' => $network->id,
            ]);
        }
    }
}
