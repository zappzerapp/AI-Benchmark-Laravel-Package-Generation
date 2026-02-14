<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Commands;

use Illuminate\Console\Command;
use VendorName\RequestShield\Facades\Shield;

final class ShieldStatsCommand extends Command
{
    protected $signature = 'shield:stats';

    protected $description = 'Display blocked request statistics for today';

    public function handle(): int
    {
        $blockedIps = Shield::getBlockedIpsCountToday();
        $blockedUserAgents = Shield::getBlockedUserAgentsCountToday();
        $totalBlocked = Shield::getTotalBlockedCountToday();

        $this->info('RequestShield - Blocked Requests Today');
        $this->newLine();
        $this->line("Blocked IPs:        {$blockedIps}");
        $this->line("Blocked User-Agents: {$blockedUserAgents}");
        $this->newLine();
        $this->warn("Total Blocked: {$totalBlocked}");

        return Command::SUCCESS;
    }
}
