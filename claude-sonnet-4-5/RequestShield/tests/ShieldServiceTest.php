<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Tests;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use VendorName\RequestShield\RequestShieldServiceProvider;
use VendorName\RequestShield\ShieldService;

final class ShieldServiceTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [RequestShieldServiceProvider::class];
    }

    public function test_ip_blocking(): void
    {
        $service = new ShieldService(
            blockedIps: ['192.168.1.100'],
            blockedUserAgents: [],
            enableLogging: false,
            logChannel: null,
        );

        $this->assertTrue($service->isIpBlocked('192.168.1.100'));
        $this->assertFalse($service->isIpBlocked('192.168.1.101'));
    }

    public function test_user_agent_blocking(): void
    {
        $service = new ShieldService(
            blockedIps: [],
            blockedUserAgents: ['badbot', 'scraperbot'],
            enableLogging: false,
            logChannel: null,
        );

        $this->assertTrue($service->isUserAgentBlocked('Mozilla/5.0 (compatible; badbot/1.0)'));
        $this->assertTrue($service->isUserAgentBlocked('ScraperBot/2.0'));
        $this->assertFalse($service->isUserAgentBlocked('Mozilla/5.0 (Windows NT 10.0)'));
    }

    public function test_should_block_request(): void
    {
        $service = new ShieldService(
            blockedIps: ['192.168.1.100'],
            blockedUserAgents: ['badbot'],
            enableLogging: false,
            logChannel: null,
        );

        $request = Request::create('/test', 'GET', [], [], [], [
            'REMOTE_ADDR' => '192.168.1.100',
        ]);

        $this->assertTrue($service->shouldBlock($request));
    }
}
