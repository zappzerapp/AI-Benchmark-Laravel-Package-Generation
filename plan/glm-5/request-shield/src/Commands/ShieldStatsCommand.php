<?php

namespace VendorName\RequestShield\Commands;

use Illuminate\Console\Command;
use VendorName\RequestShield\ShieldService;

class ShieldStatsCommand extends Command
{
    protected $signature = 'shield:stats';
    protected $description = 'Display Request Shield statistics';

    public function handle(ShieldService $service): int
    {
        $stats = $service->getStats();

        $this->info('Request Shield Statistics');
        $this->line('========================');
        $this->line("Blocked IPs: {$stats['total_ips']}");
        $this->line("Blocked User Agents: {$stats['total_user_agents']}");
        $this->line("Total Blocked Requests: {$stats['blocked_count']}");

        return self::SUCCESS;
    }
}