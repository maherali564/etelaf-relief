<?php

namespace App\Console\Commands;

use App\Services\Blockchain\BlockchainMonitorService;
use Illuminate\Console\Command;

class CheckCryptoPayments extends Command
{
    protected $signature = 'donations:check-crypto';

    protected $description = 'Check blockchain for incoming crypto payments and auto-confirm donations';

    public function handle(BlockchainMonitorService $monitor): void
    {
        $this->info('Checking blockchain for incoming crypto payments...');
        $monitor->checkAll();
        $this->info('Done.');
    }
}
