<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use VendorName\RequestShield\Contracts\ShieldInterface;

final readonly class ShieldService implements ShieldInterface
{
    private array $blockedIps;
    private array $blockedUserAgents;

    public function __construct(
        private Repository $config,
        private CacheManager $cache
    ) {
        $this->blockedIps = $this->normalizeIps($this->config->get('shield.blocked_ips', []));
        $this->blockedUserAgents = $this->config->get('shield.blocked_user_agents', []);
    }

    public function isBlocked(Request $request): bool
    {
        if ($this->isBlockedIp($request->ip())) {
            $this->logBlock('ip', $request->ip(), $request);
            $this->incrementBlockedCount();
            return true;
        }

        if ($this->isBlockedUserAgent($request->userAgent())) {
            $this->logBlock('user_agent', $request->userAgent(), $request);
            $this->incrementBlockedCount();
            return true;
        }

        return false;
    }

    public function isBlockedIp(string $ip): bool
    {
        foreach ($this->blockedIps as $blockedIp) {
            if ($this->matchesIpPattern($ip, $blockedIp)) {
                return true;
            }
        }

        return false;
    }

    public function isBlockedUserAgent(?string $userAgent): bool
    {
        if ($userAgent === null) {
            return false;
        }

        $normalizedUserAgent = strtolower($userAgent);

        foreach ($this->blockedUserAgents as $blockedUserAgent) {
            if ($this->matchesPattern($normalizedUserAgent, strtolower($blockedUserAgent))) {
                return true;
            }
        }

        return false;
    }

    public function getBlockedIps(): array
    {
        return $this->blockedIps;
    }

    public function getBlockedUserAgents(): array
    {
        return $this->blockedUserAgents;
    }

    public function addBlockedIp(string $ip): void
    {
        $this->blockedIps[] = $this->normalizeIp($ip);
    }

    public function addBlockedUserAgent(string $userAgent): void
    {
        $this->blockedUserAgents[] = $userAgent;
    }

    public function incrementBlockedCount(): void
    {
        if (!$this->config->get('shield.track_statistics', true)) {
            return;
        }

        $today = now()->format('Y-m-d');
        $cacheKey = "request-shield:blocked:{$today}";

        $this->cache->increment($cacheKey);
    }

    public function getTodayBlockedCount(): int
    {
        $today = now()->format('Y-m-d');
        $cacheKey = "request-shield:blocked:{$today}";

        return (int) $this->cache->get($cacheKey, 0);
    }

    private function normalizeIps(array $ips): array
    {
        return array_map($this->normalizeIp(...), $ips);
    }

    private function normalizeIp(string $ip): string
    {
        return trim($ip);
    }

    private function matchesIpPattern(string $ip, string $pattern): bool
    {
        if ($ip === $pattern) {
            return true;
        }

        if (str_contains($pattern, '/')) {
            return $this->matchesCidr($ip, $pattern);
        }

        if (str_contains($pattern, '*')) {
            return $this->matchesWildcard($ip, $pattern);
        }

        return false;
    }

    private function matchesCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr, 2);
        $bits = (int) $bits;

        if ($bits < 0 || $bits > 32) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = -1 << (32 - $bits);

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    private function matchesWildcard(string $ip, string $pattern): bool
    {
        $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';

        return (bool) preg_match($regex, $ip);
    }

    private function matchesPattern(string $value, string $pattern): bool
    {
        if (str_contains($pattern, '*')) {
            return $this->matchesWildcard($value, $pattern);
        }

        return $value === $pattern;
    }

    private function logBlock(string $type, string $value, Request $request): void
    {
        $logLevel = $this->config->get('shield.log_level', 'info');

        if ($logLevel === null) {
            return;
        }

        $context = [
            'type' => $type,
            'value' => $value,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
        ];

        Log::{$logLevel}('RequestShield: Blocked request', $context);
    }
}