<?php

namespace VendorName\RequestShield\Tests\Unit;

use PHPUnit\Framework\TestCase;
use VendorName\RequestShield\Console\ShieldStatsCommand;
use VendorName\RequestShield\Support\ShieldService;
use Symfony\Component\Console\Tester\CommandTester;

class ShieldStatsCommandTest extends TestCase
{
    public function test_displays_stats(): void
    {
        $shield = $this->createMock(ShieldService::class);
        $shield->method('getStats')->willReturn([
            'blocked_ips' => 5,
            'blocked_user_agents' => 3,
        ]);

        $command = new ShieldStatsCommand($shield);
        $tester = new CommandTester($command);

        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('5', $output);
        $this->assertStringContainsString('3', $output);
    }

    public function test_displays_zero_when_no_blocks(): void
    {
        $shield = $this->createMock(ShieldService::class);
        $shield->method('getStats')->willReturn([
            'blocked_ips' => 0,
            'blocked_user_agents' => 0,
        ]);

        $command = new ShieldStatsCommand($shield);
        $tester = new CommandTester($command);

        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('0', $output);
    }
}
