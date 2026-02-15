<?php

namespace VendorName\RequestShield\Tests\Feature;

use VendorName\RequestShield\Tests\TestCase;
use VendorName\RequestShield\Commands\ShieldStatsCommand;
use Illuminate\Support\Facades\Artisan;

class ShieldStatsCommandTest extends TestCase
{
    /** @test */
    public function it_displays_empty_stats(): void
    {
        $this->app['config']->set('shield.blocked_ips', []);
        $this->app['config']->set('shield.blocked_user_agents', []);

        $this->artisan('shield:stats')
            ->expectsOutput('Request Shield Statistics')
            ->expectsOutput('========================')
            ->expectsOutput('Blocked IPs: 0')
            ->expectsOutput('Blocked User Agents: 0')
            ->expectsOutput('Total Blocked Requests: 0')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_displays_correct_stats(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1', '10.0.0.2']);
        $this->app['config']->set('shield.blocked_user_agents', ['/bot/']);

        $this->artisan('shield:stats')
            ->expectsOutput('Blocked IPs: 2')
            ->expectsOutput('Blocked User Agents: 1')
            ->assertExitCode(0);
    }
}