<?php

namespace VendorName\RequestShield\Services;

class ShieldService
{
    protected array $blockedIps = [];
    protected array $blockedUserAgents = [];
    protected int $maxRequestsPerMinute = 100;
    protected array $requestCounts = [];
    protected array $blockedRequests = [];

    public function __construct()
    {
        $this->blockedIps = config('request-shield.blocked_ips', []);
        $this->blockedUserAgents = config('request-shield.blocked_user_agents', []);
        $this->maxRequestsPerMinute = config('request-shield.max_requests_per_minute', 100);
    }

    public function setBlockedIps(array $blockedIps): void
    {
        $this->blockedIps = $blockedIps;
    }

    public function setBlockedUserAgents(array $blockedUserAgents): void
    {
        $this->blockedUserAgents = $blockedUserAgents;
    }

    public function isIpBlocked(string $ip): bool
    {
        return in_array($ip, $this->blockedIps, true);
    }

    public function isUserAgentBlocked(string $userAgent): bool
    {
        return in_array($userAgent, $this->blockedUserAgents, true);
    }

    public function trackRequest(string $ip): void
    {
        if (!isset($this->requestCounts[$ip])) {
            $this->requestCounts[$ip] = 0;
        }
        $this->requestCounts[$ip]++;
    }

    public function getRequestCount(string $ip): int
    {
        return $this->requestCounts[$ip] ?? 0;
    }

    public function isRequestLimitExceeded(string $ip): bool
    {
        return $this->getRequestCount($ip) > $this->maxRequestsPerMinute;
    }

    public function shouldBlockRequest(string $ip, string $userAgent): bool
    {
        return $this->isIpBlocked($ip) || 
               $this->isUserAgentBlocked($userAgent) || 
               $this->isRequestLimitExceeded($ip);
    }

    public function logBlockedRequest(string $ip, string $userAgent, string $reason): bool
    {
        $this->blockedRequests[] = [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'reason' => $reason,
            'timestamp' => now()->toDateTimeString()
        ];
        
        return true;
    }

    public function getBlockedRequests(): array
    {
        return $this->blockedRequests;
    }
}