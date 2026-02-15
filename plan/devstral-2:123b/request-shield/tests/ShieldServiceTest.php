<?php

namespace VendorName\RequestShield\Tests;

use VendorName\RequestShield\Services\ShieldService;
use PHPUnit\Framework\TestCase;

class ShieldServiceTest extends TestCase
{
    protected ShieldService $shieldService;

    protected function setUp(): void
    {
        $this->shieldService = new ShieldService();
    }

    public function testIsIpBlockedWithBlockedIp()
    {
        $blockedIps = ['192.168.1.100', '10.0.0.5'];
        $this->shieldService->setBlockedIps($blockedIps);
        
        $this->assertTrue($this->shieldService->isIpBlocked('192.168.1.100'));
    }

    public function testIsIpBlockedWithNonBlockedIp()
    {
        $blockedIps = ['192.168.1.100', '10.0.0.5'];
        $this->shieldService->setBlockedIps($blockedIps);
        
        $this->assertFalse($this->shieldService->isIpBlocked('192.168.1.200'));
    }

    public function testIsUserAgentBlockedWithBlockedUserAgent()
    {
        $blockedUserAgents = ['malicious-bot', 'evil-crawler'];
        $this->shieldService->setBlockedUserAgents($blockedUserAgents);
        
        $this->assertTrue($this->shieldService->isUserAgentBlocked('malicious-bot'));
    }

    public function testIsUserAgentBlockedWithNonBlockedUserAgent()
    {
        $blockedUserAgents = ['malicious-bot', 'evil-crawler'];
        $this->shieldService->setBlockedUserAgents($blockedUserAgents);
        
        $this->assertFalse($this->shieldService->isUserAgentBlocked('good-bot'));
    }

    public function testIsRequestLimitExceeded()
    {
        $this->shieldService->trackRequest('192.168.1.1');
        
        // Track 100 requests to exceed limit of 100
        for ($i = 0; $i < 100; $i++) {
            $this->shieldService->trackRequest('192.168.1.1');
        }
        
        $this->assertTrue($this->shieldService->isRequestLimitExceeded('192.168.1.1'));
    }

    public function testIsRequestLimitNotExceeded()
    {
        $this->shieldService->trackRequest('192.168.1.1');
        
        // Track only 50 requests (under limit of 100)
        for ($i = 0; $i < 50; $i++) {
            $this->shieldService->trackRequest('192.168.1.1');
        }
        
        $this->assertFalse($this->shieldService->isRequestLimitExceeded('192.168.1.1'));
    }

    public function testShouldBlockRequestWhenIpBlocked()
    {
        $blockedIps = ['192.168.1.100'];
        $this->shieldService->setBlockedIps($blockedIps);
        
        $this->assertTrue($this->shieldService->shouldBlockRequest('192.168.1.100', 'good-bot'));
    }

    public function testShouldBlockRequestWhenUserAgentBlocked()
    {
        $blockedUserAgents = ['malicious-bot'];
        $this->shieldService->setBlockedUserAgents($blockedUserAgents);
        
        $this->assertTrue($this->shieldService->shouldBlockRequest('192.168.1.1', 'malicious-bot'));
    }

    public function testShouldBlockRequestWhenRequestLimitExceeded()
    {
        // Track 101 requests to exceed limit
        for ($i = 0; $i < 101; $i++) {
            $this->shieldService->trackRequest('192.168.1.1');
        }
        
        $this->assertTrue($this->shieldService->shouldBlockRequest('192.168.1.1', 'good-bot'));
    }

    public function testShouldNotBlockRequestWhenSafe()
    {
        $this->assertFalse($this->shieldService->shouldBlockRequest('192.168.1.1', 'good-bot'));
    }

    public function testLogBlockedRequest()
    {
        $ip = '192.168.1.100';
        $userAgent = 'malicious-bot';
        $reason = 'IP blocked';
        
        $result = $this->shieldService->logBlockedRequest($ip, $userAgent, $reason);
        
        $this->assertTrue($result);
        $this->assertCount(1, $this->shieldService->getBlockedRequests());
    }

    public function testGetBlockedRequests()
    {
        $this->shieldService->logBlockedRequest('192.168.1.100', 'bot1', 'IP blocked');
        $this->shieldService->logBlockedRequest('192.168.1.101', 'bot2', 'User agent blocked');
        
        $blockedRequests = $this->shieldService->getBlockedRequests();
        
        $this->assertCount(2, $blockedRequests);
        $this->assertEquals('192.168.1.100', $blockedRequests[0]['ip']);
        $this->assertEquals('bot1', $blockedRequests[0]['user_agent']);
        $this->assertEquals('IP blocked', $blockedRequests[0]['reason']);
    }

    public function testGetRequestCount()
    {
        $this->shieldService->trackRequest('192.168.1.1');
        $this->shieldService->trackRequest('192.168.1.1');
        $this->shieldService->trackRequest('192.168.1.1');
        
        $this->assertEquals(3, $this->shieldService->getRequestCount('192.168.1.1'));
    }
}