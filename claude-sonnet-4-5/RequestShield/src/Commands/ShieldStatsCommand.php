<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Commands;

use Illuminate\Console\Command;
use VendorName\RequestShield\Facades\Shield;

final class ShieldStatsCommand extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'shield:stats
                            {--date= : The date to get stats for (Y-m-d format)}
                            {--last-days=7 : Show stats for the last N days}';

    /**
     * The console command description
     */
    protected $description = 'Display RequestShield blocking statistics';

    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $this->info('🛡️  RequestShield Statistics');
        $this->newLine();

        if ($date = $this->option('date')) {
            $this->displaySingleDay($date);
        } else {
            $this->displayMultipleDays((int) $this->option('last-days'));
        }

        $this->newLine();
        $this->displayConfiguration();

        return self::SUCCESS;
    }

    /**
     * Display stats for a single day
     */
    private function displaySingleDay(string $date): void
    {
        $count = Shield::getBlockedCount($date);

        $this->table(
            ['Date', 'Blocked Requests'],
            [[$date, $count]]
        );
    }

    /**
     * Display stats for multiple days
     */
    private function displayMultipleDays(int $days): void
    {
        $stats = [];
        $totalBlocked = 0;

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = Shield::getBlockedCount($date);
            $totalBlocked += $count;

            $stats[] = [
                'date' => $date,
                'count' => $count,
                'bar' => str_repeat('█', min($count, 50)),
            ];
        }

        $this->table(
            ['Date', 'Blocked Requests', 'Graph'],
            array_map(fn($stat) => [
                $stat['date'],
                $stat['count'],
                $stat['bar'],
            ], $stats)
        );

        $this->info("Total blocked in last {$days} days: {$totalBlocked}");
    }

    /**
     * Display current configuration
     */
    private function displayConfiguration(): void
    {
        $this->comment('Current Configuration:');

        $blockedIps = config('shield.blocked_ips', []);
        $blockedUserAgents = config('shield.blocked_user_agents', []);

        $this->line('📍 Blocked IPs: ' . (count($blockedIps) > 0 ? count($blockedIps) : 'None'));
        if (count($blockedIps) > 0) {
            foreach ($blockedIps as $ip) {
                $this->line("   - {$ip}");
            }
        }

        $this->newLine();

        $this->line('🤖 Blocked User-Agents: ' . (count($blockedUserAgents) > 0 ? count($blockedUserAgents) : 'None'));
        if (count($blockedUserAgents) > 0) {
            foreach ($blockedUserAgents as $ua) {
                $this->line("   - {$ua}");
            }
        }

        $this->newLine();
        $this->line('📊 Logging: ' . (config('shield.enable_logging') ? '✅ Enabled' : '❌ Disabled'));
        $this->line('📝 Response Type: ' . config('shield.response_type', 'exception'));
    }
}
