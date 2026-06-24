<?php

namespace App\Services\Payment;

use App\Models\Donation;

class ManualService
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function process(Donation $donation): array
    {
        return [
            'type' => 'manual',
            'message' => 'سيتم التواصل معك لتأكيد التبرع. شكراً لك!',
        ];
    }
}
