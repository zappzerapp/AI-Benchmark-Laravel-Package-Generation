<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final readonly class ShieldService
{
    public function __construct(
        private array $blockedIps,
        private array $blockedUserAgents,
        private bool $enableLogging,
        private ?string $logChannel,
    ) {
    }

    /**
     * Check if the request should be blocked
     */
    public function shouldBlock(Request $request): bool
    {
        return $this->isIpBlocked($request->ip())
            || $this->isUserAgentBlocked($request->userAgent());
    }

    /**
     * Check if IP address is blocked
     */
    public function isIpBlocked(?string $ip): bool
    {
        if ($ip === null) {
            return false;
        }

        return in_array($ip, $this->blockedIps, true);
    }

    /**
     * Check if User-Agent is blocked (case-insensitive partial match)
     */
    public function isUserAgentBlocked(?string $userAgent): bool
    {
        if ($userAgent === null) {
            return false;
        }

        $userAgentLower = strtolower($userAgent);

        foreach ($this->blockedUserAgents as $blockedAgent) {
            if (str_contains($userAgentLower, strtolower($blockedAgent))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log a blocked request
     */
    public function logBlockedRequest(Request $request, string $reason): void
    {
        if (!$this->enableLogging) {
            return;
        }

        $cacheKey = 'shield:blocked_requests:' . now()->format('Y-m-d');
        Cache::increment($cacheKey, 1);
        Cache::put($cacheKey, Cache::get($cacheKey, 0), now()->addDays(30));

        $logger = $this->logChannel ? Log::channel($this->logChannel) : Log::getFacadeRoot();

        $logger->warning('RequestShield: Blocked request', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'reason' => $reason,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get blocked requests count for a specific date
     */
    public function getBlockedCount(?string $date = null): int
    {
        $date = $date ?? now()->format('Y-m-d');
        $cacheKey = 'shield:blocked_requests:' . $date;

        return (int) Cache::get($cacheKey, 0);
    }

    /**
     * Add an IP to the blocked list dynamically
     */
    public function blockIp(string $ip): void
    {
        $currentBlocked = config('shield.blocked_ips', []);
        
        if (!in_array($ip, $currentBlocked, true)) {
            $currentBlocked[] = $ip;
            config(['shield.blocked_ips' => $currentBlocked]);
        }
    }

    /**
     * Remove an IP from the blocked list dynamically
     */
    public function unblockIp(string $ip): void
    {
        $currentBlocked = config('shield.blocked_ips', []);
        $filtered = array_filter($currentBlocked, fn($blockedIp) => $blockedIp !== $ip);
        
        config(['shield.blocked_ips' => array_values($filtered)]);
    }
}
