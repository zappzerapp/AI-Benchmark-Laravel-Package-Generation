<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use VendorName\RequestShield\ShieldService;
use VendorName\RequestShield\Tests\TestCase;

final class ShieldServiceTest extends TestCase
{
    private ShieldService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ShieldService(
            blockedIps: ['192.168.1.100', '10.0.0.5'],
            blockedUserAgents: ['BadBot', 'EvilScraper'],
        );
    }

    #[Test]
    public function it_blocks_a_listed_ip(): void
    {
        $this->assertTrue($this->service->isIpBlocked('192.168.1.100'));
    }

    #[Test]
    public function it_allows_a_non_listed_ip(): void
    {
        $this->assertFalse($this->service->isIpBlocked('8.8.8.8'));
    }

    #[Test]
    public function it_blocks_a_matching_user_agent(): void
    {
        $this->assertTrue($this->service->isUserAgentBlocked('Mozilla/5.0 BadBot/1.0'));
    }

    #[Test]
    public function it_allows_a_clean_user_agent(): void
    {
        $this->assertFalse($this->service->isUserAgentBlocked('Mozilla/5.0 Chrome/120'));
    }

    #[Test]
    public function user_agent_check_is_case_insensitive(): void
    {
        $this->assertTrue($this->service->isUserAgentBlocked('Mozilla/5.0 badbot/1.0'));
    }

    #[Test]
    public function it_detects_blocked_request_by_ip(): void
    {
        $this->assertTrue($this->service->shouldBlock('192.168.1.100', 'Mozilla/5.0'));
    }

    #[Test]
    public function it_detects_blocked_request_by_user_agent(): void
    {
        $this->assertTrue($this->service->shouldBlock('8.8.8.8', 'EvilScraper/2.0'));
    }

    #[Test]
    public function it_allows_clean_request(): void
    {
        $this->assertFalse($this->service->shouldBlock('8.8.8.8', 'Mozilla/5.0 Chrome/120'));
    }

    #[Test]
    public function it_increments_blocked_count(): void
    {
        $this->assertEquals(0, $this->service->getBlockedCount());

        $this->service->recordBlocked();
        $this->service->recordBlocked();

        $this->assertEquals(2, $this->service->getBlockedCount());
    }

    #[Test]
    public function it_resets_blocked_count(): void
    {
        $this->service->recordBlocked();
        $this->service->resetBlockedCount();

        $this->assertEquals(0, $this->service->getBlockedCount());
    }
}
