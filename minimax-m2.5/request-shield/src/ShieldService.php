<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class ShieldService
{
    private const BLOCKED_IPS_CACHE_KEY = 'request_shield_blocked_ips_count';
    private const BLOCKED_USER_AGENTS_CACHE_KEY = 'request_shield_blocked_ua_count';
    private const DAILY_CACHE_PREFIX = 'request_shield_daily_';

    /**
     * @var array<string>
     */
    private array $blockedIps;

    /**
     * @var array<string>
     */
    private array $blockedUserAgents;

    private bool $enableLogging;
    private bool $returnView;

    public function __construct(
        array $blockedIps = [],
        array $blockedUserAgents = [],
        bool $enableLogging = true,
        bool $returnView = true
    ) {
        $this->blockedIps = $blockedIps;
        $this->blockedUserAgents = $blockedUserAgents;
        $this->enableLogging = $enableLogging;
        $this->returnView = $returnView;
    }

    /**
     * Check if the request should be blocked
     */
    public function shouldBlock(Request $request): bool
    {
        return $this->isIpBlocked($request) || $this->isUserAgentBlocked($request);
    }

    /**
     * Check if the request IP is blocked
     */
    public function isIpBlocked(Request $request): bool
    {
        $clientIp = $request->ip();

        if ($clientIp === null) {
            return false;
        }

        foreach ($this->blockedIps as $blockedIp) {
            if ($this->matchesIpPattern($clientIp, $blockedIp)) {
                $this->incrementBlockedCount(self::BLOCKED_IPS_CACHE_KEY);
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the request User-Agent is blocked
     */
    public function isUserAgentBlocked(Request $request): bool
    {
        $userAgent = $request->userAgent();

        if ($userAgent === '') {
            return false;
        }

        foreach ($this->blockedUserAgents as $blockedAgent) {
            if (mb_stripos($userAgent, $blockedAgent) !== false) {
                $this->incrementBlockedCount(self::BLOCKED_USER_AGENTS_CACHE_KEY);
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP matches a pattern (supports CIDR notation and wildcards)
     */
    private function matchesIpPattern(string $clientIp, string $pattern): bool
    {
        // Exact match
        if ($clientIp === $pattern) {
            return true;
        }

        // CIDR notation (e.g., 192.168.1.0/24)
        if (str_contains($pattern, '/')) {
            return $this->matchesCidr($clientIp, $pattern);
        }

        // Wildcard support (e.g., 192.168.*.*)
        if (str_contains($pattern, '*')) {
            return $this->matchesWildcard($clientIp, $pattern);
        }

        return false;
    }

    /**
     * Check if IP matches CIDR notation
     */
    private function matchesCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);
        
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        
        if ($ip === false || $subnet === false) {
            return false;
        }
        
        $mask = -1 << (32 - (int) $bits);
        
        return ($ip & $mask) === ($subnet & $mask);
    }

    /**
     * Check if IP matches wildcard pattern
     */
    private function matchesWildcard(string $ip, string $pattern): bool
    {
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace(['\\*', '\\?'], ['.*', '.'], $pattern);
        
        return (bool) preg_match("/^{$pattern}$/i", $ip);
    }

    /**
     * Increment the blocked request count
     */
    private function incrementBlockedCount(string $key): void
    {
        $today = now()->format('Y-m-d');
        $cacheKey = self::DAILY_CACHE_PREFIX . $today . '_' . $key;

        try {
            Cache::increment($cacheKey);
        } catch (\Throwable $e) {
            // Cache not available, silently fail
        }
    }

    /**
     * Get blocked IPs count for today
     */
    public function getBlockedIpsCountToday(): int
    {
        return $this->getDailyCount(self::BLOCKED_IPS_CACHE_KEY);
    }

    /**
     * Get blocked User-Agents count for today
     */
    public function getBlockedUserAgentsCountToday(): int
    {
        return $this->getDailyCount(self::BLOCKED_USER_AGENTS_CACHE_KEY);
    }

    /**
     * Get total blocked count for today
     */
    public function getTotalBlockedCountToday(): int
    {
        return $this->getBlockedIpsCountToday() + $this->getBlockedUserAgentsCountToday();
    }

    /**
     * Get daily count from cache
     */
    private function getDailyCount(string $key): int
    {
        $today = now()->format('Y-m-d');
        $cacheKey = self::DAILY_CACHE_PREFIX . $today . '_' . $key;

        try {
            return (int) Cache::get($cacheKey, 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Get blocked IPs from config
     *
     * @return array<string>
     */
    public function getBlockedIps(): array
    {
        return $this->blockedIps;
    }

    /**
     * Get blocked User-Agents from config
     *
     * @return array<string>
     */
    public function getBlockedUserAgents(): array
    {
        return $this->blockedUserAgents;
    }

    /**
     * Check if logging is enabled
     */
    public function isLoggingEnabled(): bool
    {
        return $this->enableLogging;
    }

    /**
     * Check if view should be returned instead of exception
     */
    public function shouldReturnView(): bool
    {
        return $this->returnView;
    }
}
