<?php

namespace VendorName\RequestShield\Commands;

use Illuminate\Console\Command;
use VendorName\RequestShield\ShieldService;

class ShieldStatsCommand extends Command
{
    protected $signature = 'shield:stats';

    protected $description = 'Display RequestShield statistics';

    public function __construct(
        protected ShieldService $shieldService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $blockedCount = $this->shieldService->getBlockedCount();

        $this->info("RequestShield Statistics:");
        $this->info("Blocked requests today: {$blockedCount}");

        return self::SUCCESS;
    }
}