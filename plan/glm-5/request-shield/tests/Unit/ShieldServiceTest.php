<?php

namespace VendorName\RequestShield\Tests\Unit;

use VendorName\RequestShield\Tests\TestCase;
use VendorName\RequestShield\ShieldService;

class ShieldServiceTest extends TestCase
{
    private ShieldService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(ShieldService::class);
    }

    /** @test */
    public function it_detects_blocked_ip(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['192.168.1.100']);

        $this->assertTrue($this->service->isIpBlocked('192.168.1.100'));
        $this->assertFalse($this->service->isIpBlocked('192.168.1.101'));
    }

    /** @test */
    public function it_detects_blocked_user_agent(): void
    {
        $this->app['config']->set('shield.blocked_user_agents', ['/^badbot/i', '/scraper/i']);

        $this->assertTrue($this->service->isUserAgentBlocked('BadBot/1.0'));
        $this->assertTrue($this->service->isUserAgentBlocked('WebScraper 2.0'));
        $this->assertFalse($this->service->isUserAgentBlocked('Mozilla/5.0'));
    }

    /** @test */
    public function should_block_returns_true_when_ip_blocked(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);

        $this->assertTrue($this->service->shouldBlock('10.0.0.1', 'Mozilla/5.0'));
    }

    /** @test */
    public function should_block_returns_true_when_user_agent_blocked(): void
    {
        $this->app['config']->set('shield.blocked_user_agents', ['/^malicious/i']);

        $this->assertTrue($this->service->shouldBlock('192.168.1.1', 'MaliciousBot/1.0'));
    }

    /** @test */
    public function should_block_returns_false_when_not_blocked(): void
    {
        $this->app['config']->set('shield.blocked_ips', []);
        $this->app['config']->set('shield.blocked_user_agents', []);

        $this->assertFalse($this->service->shouldBlock('192.168.1.1', 'Mozilla/5.0'));
    }

    /** @test */
    public function it_returns_empty_stats_when_no_blocks(): void
    {
        $stats = $this->service->getStats();

        $this->assertEquals(0, $stats['total_ips']);
        $this->assertEquals(0, $stats['total_user_agents']);
        $this->assertEquals(0, $stats['blocked_count']);
    }

    /** @test */
    public function it_returns_correct_stats(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1', '10.0.0.2']);
        $this->app['config']->set('shield.blocked_user_agents', ['/bot/']);

        $stats = $this->service->getStats();

        $this->assertEquals(2, $stats['total_ips']);
        $this->assertEquals(1, $stats['total_user_agents']);
    }

    /** @test */
    public function it_records_blocked_requests(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);

        $this->service->recordBlocked('10.0.0.1', 'BadBot/1.0');
        $stats = $this->service->getStats();

        $this->assertEquals(1, $stats['blocked_count']);
    }

    /** @test */
    public function it_handles_empty_ip_list(): void
    {
        $this->assertFalse($this->service->isIpBlocked(''));
        $this->assertFalse($this->service->isIpBlocked('any-ip'));
    }

    /** @test */
    public function it_handles_empty_user_agent(): void
    {
        $this->app['config']->set('shield.blocked_user_agents', ['/bot/']);

        $this->assertFalse($this->service->isUserAgentBlocked(''));
    }
}