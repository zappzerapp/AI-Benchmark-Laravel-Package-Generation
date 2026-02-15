<?php

namespace VendorName\RequestShield\Console;

use VendorName\RequestShield\Support\ShieldService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShieldStatsCommand extends Command
{
    private ShieldService $shield;

    public function __construct(ShieldService $shield)
    {
        parent::__construct('shield:stats');
        $this->shield = $shield;
    }

    protected function configure(): void
    {
        $this->setDescription('Display request shield statistics');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stats = $this->shield->getStats();

        $output->writeln('Request Shield Statistics');
        $output->writeln('==========================');
        $output->writeln("Blocked IPs: {$stats['blocked_ips']}");
        $output->writeln("Blocked User-Agents: {$stats['blocked_user_agents']}");

        return Command::SUCCESS;
    }
}
