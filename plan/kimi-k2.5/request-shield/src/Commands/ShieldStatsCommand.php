<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Commands;

use Illuminate\Console\Command;
use VendorName\RequestShield\ShieldService;

final class ShieldStatsCommand extends Command
{
    protected $signature = 'shield:stats';

    protected $description = 'Display the number of requests blocked by RequestShield today';

    public function __construct(
        private readonly ShieldService $shield,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = $this->shield->getBlockedCount();

        $this->components->info("RequestShield Statistics");
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Blocked requests (this session)', (string) $count],
                ['Blocked IPs configured', (string) count(config('shield.blocked_ips', []))],
                ['Blocked User-Agents configured', (string) count(config('shield.blocked_user_agents', []))],
            ],
        );

        return self::SUCCESS;
    }
}
