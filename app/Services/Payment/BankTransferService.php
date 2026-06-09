<?php

namespace App\Services\Payment;

use App\Models\Donation;

class BankTransferService
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getInstructions(): array
    {
        return [
            'bank_name' => $this->config['bank_name'] ?? '',
            'account_name' => $this->config['account_name'] ?? '',
            'account_number' => $this->config['account_number'] ?? '',
            'iban' => $this->config['iban'] ?? '',
            'swift_code' => $this->config['swift_code'] ?? '',
        ];
    }

    public function process(Donation $donation): array
    {
        return [
            'type' => 'bank_transfer',
            'instructions' => $this->getInstructions(),
            'message' => 'يرجى تحويل المبلغ إلى الحساب البنكي أدناه والتواصل معنا لتأكيد التبرع',
        ];
    }
}
