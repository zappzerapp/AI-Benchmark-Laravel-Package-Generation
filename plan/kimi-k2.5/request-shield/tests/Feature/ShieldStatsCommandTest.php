<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use VendorName\RequestShield\ShieldService;
use VendorName\RequestShield\Tests\TestCase;

final class ShieldStatsCommandTest extends TestCase
{
    #[Test]
    public function it_displays_zero_blocked_when_no_requests_blocked(): void
    {
        $this->artisan('shield:stats')
            ->expectsOutputToContain('0')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_displays_the_current_blocked_count(): void
    {
        /** @var ShieldService $service */
        $service = $this->app->make(ShieldService::class);
        $service->recordBlocked();
        $service->recordBlocked();
        $service->recordBlocked();

        $this->artisan('shield:stats')
            ->expectsOutputToContain('3')
            ->assertExitCode(0);
    }
}
