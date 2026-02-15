<?php

namespace VendorName\RequestShield\Tests\Unit;

use PHPUnit\Framework\TestCase;
use VendorName\RequestShield\Support\ShieldService;

class ShieldServiceTest extends TestCase
{
    private ShieldService $shield;

    protected function setUp(): void
    {
        parent::setUp();
        $config = [
            'enabled' => true,
            'blocked_ips' => ['192.168.1.100', '10.0.0.0/24'],
            'blocked_user_agents' => ['curl', 'python-requests'],
            'response_view' => 'request-shield::blocked',
            'response_status' => 403,
        ];
        $this->shield = new ShieldService($config);
    }

    public function test_is_enabled_returns_true_when_enabled(): void
    {
        $this->assertTrue($this->shield->isEnabled());
    }

    public function test_is_enabled_returns_false_when_disabled(): void
    {
        $config = ['enabled' => false];
        $shield = new ShieldService($config);
        $this->assertFalse($shield->isEnabled());
    }

    public function test_should_block_returns_true_for_blocked_ip(): void
    {
        $this->assertTrue($this->shield->shouldBlock('192.168.1.100'));
    }

    public function test_should_block_returns_false_for_allowed_ip(): void
    {
        $this->assertFalse($this->shield->shouldBlock('192.168.1.200'));
    }

    public function test_should_block_returns_true_for_ip_in_cidr_range(): void
    {
        $this->assertTrue($this->shield->shouldBlock('10.0.0.50'));
    }

    public function test_should_block_returns_true_for_blocked_user_agent(): void
    {
        $request = $this->createMockRequest('192.168.1.1', 'curl/7.68.0');
        $this->assertTrue($this->shield->shouldBlockRequest($request));
    }

    public function test_should_block_returns_true_for_blocked_ip_via_request(): void
    {
        $request = $this->createMockRequest('192.168.1.100', 'Mozilla/5.0');
        $this->assertTrue($this->shield->shouldBlockRequest($request));
    }

    public function test_should_block_returns_false_for_allowed_request(): void
    {
        $request = $this->createMockRequest('192.168.1.200', 'Mozilla/5.0');
        $this->assertFalse($this->shield->shouldBlockRequest($request));
    }

    public function test_should_block_returns_false_when_shield_disabled(): void
    {
        $config = ['enabled' => false, 'blocked_ips' => ['192.168.1.100']];
        $shield = new ShieldService($config);
        $request = $this->createMockRequest('192.168.1.100', 'Mozilla/5.0');
        $this->assertFalse($shield->shouldBlockRequest($request));
    }

    public function test_get_blocked_ips_returns_configured_ips(): void
    {
        $ips = $this->shield->getBlockedIps();
        $this->assertContains('192.168.1.100', $ips);
        $this->assertContains('10.0.0.0/24', $ips);
    }

    public function test_get_blocked_user_agents_returns_configured_uas(): void
    {
        $uas = $this->shield->getBlockedUserAgents();
        $this->assertContains('curl', $uas);
        $this->assertContains('python-requests', $uas);
    }

    public function test_get_response_status_returns_configured_status(): void
    {
        $this->assertEquals(403, $this->shield->getResponseStatus());
    }

    public function test_get_response_view_returns_configured_view(): void
    {
        $this->assertEquals('request-shield::blocked', $this->shield->getResponseView());
    }

    public function test_record_block_increments_stats(): void
    {
        $this->shield->recordBlock('ip', '192.168.1.100');
        $this->shield->recordBlock('user-agent', 'curl');

        $stats = $this->shield->getStats();
        $this->assertEquals(1, $stats['blocked_ips']);
        $this->assertEquals(1, $stats['blocked_user_agents']);
    }

    public function test_get_stats_returns_zero_for_fresh_instance(): void
    {
        $stats = $this->shield->getStats();
        $this->assertEquals(0, $stats['blocked_ips']);
        $this->assertEquals(0, $stats['blocked_user_agents']);
    }

    private function createMockRequest(string $ip, string $userAgent): object
    {
        return new class($ip, $userAgent) {
            public function __construct(public string $ip, public string $userAgent) {}
            public function ip(): string { return $this->ip; }
            public function userAgent(): string { return $this->userAgent; }
        };
    }
}
