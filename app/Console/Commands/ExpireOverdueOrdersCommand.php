<?php

namespace App\Console\Commands;

use App\Services\MidtransService;
use Illuminate\Console\Command;

class ExpireOverdueOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire overdue unpaid online orders and release reserved stock';

    /**
     * Execute the console command.
     */
    public function handle(MidtransService $midtransService): int
    {
        $this->info('Checking for overdue unpaid orders...');

        $expiredCount = $midtransService->expireOverdueOrders();

        if ($expiredCount > 0) {
            $this->info("Successfully expired {$expiredCount} overdue order(s) and released reserved stock.");
        } else {
            $this->line('No overdue orders found.');
        }

        return self::SUCCESS;
    }
}
