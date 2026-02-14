<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Commands;

use Illuminate\Console\Command;
use VendorName\RequestShield\ShieldService;

final class ShieldStatsCommand extends Command
{
    protected $signature = 'shield:stats';

    protected $description = 'Display how many requests have been blocked today by RequestShield';

    public function handle(ShieldService $shield): int
    {
        $count = $shield->blockedTodayCount();

        $this->components->info("RequestShield Statistics");

        $this->table(
            ['Metric', 'Value'],
            [
                ['Date', now()->format('Y-m-d')],
                ['Blocked requests today', (string) $count],
                ['Blocked IPs configured', (string) count(config('shield.blocked_ips', []))],
                ['Blocked User-Agents configured', (string) count(config('shield.blocked_user_agents', []))],
            ],
        );

        return self::SUCCESS;
    }
}
