<?php

namespace App\Console\Commands;

use App\Services\RenewalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireStaleOrders extends Command
{
    protected $signature = 'orders:expire-stale';
    protected $description = '將逾時未付款的 pending 訂單標記為 expired';

    public function __construct(private RenewalService $renewalService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = $this->renewalService->expireStaleOrders();

        if ($count > 0) {
            $this->info("已清理 {$count} 筆逾期訂單");
            Log::info('逾期訂單清理完成', ['count' => $count]);
        } else {
            $this->info('無逾期訂單需清理');
        }

        return Command::SUCCESS;
    }
}
