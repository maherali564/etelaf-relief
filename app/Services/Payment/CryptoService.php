<?php

namespace App\Services\Payment;

use App\Models\Donation;

class CryptoService
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getPaymentInfo(): array
    {
        return [
            'wallet_address' => $this->config['wallet_address'] ?? '',
            'network' => $this->config['network'] ?? 'TRC20',
            'currency_symbol' => $this->config['currency_symbol'] ?? 'USDT',
            'min_amount' => $this->config['min_amount'] ?? null,
            'conversion_rate' => $this->config['conversion_rate'] ?? 1,
            'qr_code' => $this->config['qr_code'] ?? null,
            'additional_info' => $this->config['additional_info'] ?? '',
        ];
    }

    public function process(Donation $donation): array
    {
        $info = $this->getPaymentInfo();
        $cryptoAmount = $info['conversion_rate'] > 0
            ? round($donation->amount * (float) $info['conversion_rate'], 6)
            : $donation->amount;

        return [
            'type' => 'crypto',
            'info' => $info,
            'crypto_amount' => $cryptoAmount,
            'message' => "يرجى تحويل {$cryptoAmount} {$info['currency_symbol']} إلى عنوان المحفظة أدناه",
        ];
    }
}
