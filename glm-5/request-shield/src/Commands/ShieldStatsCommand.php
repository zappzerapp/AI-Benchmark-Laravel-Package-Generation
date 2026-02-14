<?php

namespace VendorName\RequestShield\Commands;

use Illuminate\Console\Command;
use VendorName\RequestShield\ShieldService;

final class ShieldStatsCommand extends Command
{
    protected $signature = 'shield:stats
        {--today : Show only today\'s statistics}
        {--reset : Reset all statistics}';

    protected $description = 'Display RequestShield blocking statistics';

    public function handle(ShieldService $shield): int
    {
        if ($this->option('reset')) {
            return $this->resetStats();
        }

        return $this->showStats($shield);
    }

    private function showStats(ShieldService $shield): int
    {
        $stats = $shield->getDailyStats();

        $this->newLine();
        $this->info('🛡️  RequestShield Statistics');
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Date', $stats['date'] ?? today()->toDateString()],
                ['Requests Blocked Today', $stats['blocked_count'] ?? 0],
                ['Driver', config('shield.statistics.driver', 'file')],
            ]
        );

        $this->newLine();

        $blockedCount = $stats['blocked_count'] ?? 0;

        if ($blockedCount > 100) {
            $this->warn('⚠️  High number of blocked requests detected. Consider reviewing your rules.');
        } elseif ($blockedCount > 0) {
            $this->line("<info>✓</info> Protection is active and working.");
        } else {
            $this->line("<comment>ℹ</comment> No requests blocked today.");
        }

        return self::SUCCESS;
    }

    private function resetStats(): int
    {
        $path = config('shield.statistics.file_path', storage_path('framework/shield-stats.json'));

        if (file_exists($path)) {
            unlink($path);
            $this->info('✓ Statistics have been reset.');
        } else {
            $this->comment('ℹ No statistics file found to reset.');
        }

        return self::SUCCESS;
    }
}