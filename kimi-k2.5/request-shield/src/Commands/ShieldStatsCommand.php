<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use VendorName\RequestShield\Contracts\ShieldInterface;

final class ShieldStatsCommand extends Command
{
    protected $signature = 'shield:stats
                            {--date=today : Das Datum für die Statistik (format: Y-m-d)}
                            {--yesterday : Zeige die Statistik für gestern}
                            {--all : Zeige die Statistik für alle verfügbaren Tage}';

    protected $description = 'Zeigt Statistiken über blockierte Requests an';

    public function __construct(
        private readonly ShieldInterface $shield
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('yesterday')) {
            $date = Carbon::yesterday()->format('Y-m-d');
        } elseif ($this->option('date') !== 'today') {
            $date = $this->option('date');
        } else {
            $date = Carbon::now()->format('Y-m-d');
        }

        if ($this->option('all')) {
            $this->showAllStats();
            return self::SUCCESS;
        }

        $count = $this->shield->getTodayBlockedCount();

        $this->info("╔════════════════════════════════════╗");
        $this->info("║     Request Shield Statistics      ║");
        $this->info("╠════════════════════════════════════╣");
        $this->info(sprintf("║ Datum: %-27s ║", $date));
        $this->info(sprintf("║ Blockierte Requests: %-13s ║", number_format($count)));
        $this->info("╚════════════════════════════════════╝");

        if ($count === 0) {
            $this->newLine();
            $this->warn('Keine Requests wurden an diesem Tag blockiert.');
        } else {
            $this->newLine();
            $this->info('Hinweis: Diese Statistiken werden vom Cache gespeichert.');
        }

        return self::SUCCESS;
    }

    private function showAllStats(): void
    {
        $this->info("╔════════════════════════════════════╗");
        $this->info("║     Request Shield Statistics      ║");
        $this->info("║           (Alle Tage)              ║");
        $this->info("╠════════════════════════════════════╣");
        $this->info(sprintf("║ Heute: %-27s ║", number_format($this->shield->getTodayBlockedCount())));
        $this->info("╚════════════════════════════════════╝");
    }
}