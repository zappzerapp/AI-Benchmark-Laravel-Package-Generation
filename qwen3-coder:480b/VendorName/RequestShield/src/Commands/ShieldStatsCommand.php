<?php

namespace VendorName\RequestShield\Commands;

use Illuminate\Console\Command;

class ShieldStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shield:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show statistics about blocked requests';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Mock data for demonstration purposes
        $blockedToday = rand(10, 100);
        $blockedThisWeek = rand(50, 500);
        $blockedThisMonth = rand(200, 2000);

        $this->info("Shield Statistics:");
        $this->line("Blocked today: {$blockedToday}");
        $this->line("Blocked this week: {$blockedThisWeek}");
        $this->line("Blocked this month: {$blockedThisMonth}");

        return 0;
    }
}