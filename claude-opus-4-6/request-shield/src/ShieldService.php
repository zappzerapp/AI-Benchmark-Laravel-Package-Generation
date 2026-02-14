<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class ShieldService
{
    private const CACHE_KEY_BLOCKED_TODAY = 'request_shield:blocked_count:';

    public function __construct(
        private readonly array $blockedIps,
        private readonly array $blockedUserAgents,
        private readonly bool $logBlocked,
        private readonly ?string $cacheStore,
    ) {}

    public function isBlocked(Request $request): bool
    {
        return $this->isIpBlocked($request->ip() ?? '') || $this->isUserAgentBlocked($request->userAgent() ?? '');
    }

    public function isIpBlocked(string $ip): bool
    {
        foreach ($this->blockedIps as $rule) {
            if (str_contains($rule, '/')) {
                if ($this->ipMatchesCidr($ip, $rule)) {
                    return true;
                }
            } elseif ($ip === $rule) {
                return true;
            }
        }

        return false;
    }

    public function isUserAgentBlocked(string $userAgent): bool
    {
        foreach ($this->blockedUserAgents as $pattern) {
            if (mb_stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    public function recordBlocked(Request $request): void
    {
        if (! $this->logBlocked) {
            return;
        }

        Log::warning('RequestShield blocked request', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
        ]);

        $cacheKey = self::CACHE_KEY_BLOCKED_TODAY . now()->format('Y-m-d');

        $this->cache()->increment($cacheKey);
        $this->cache()->put($cacheKey, (int) $this->cache()->get($cacheKey, 0), now()->endOfDay());
    }

    public function blockedTodayCount(): int
    {
        $cacheKey = self::CACHE_KEY_BLOCKED_TODAY . now()->format('Y-m-d');

        return (int) $this->cache()->get($cacheKey, 0);
    }

    private function cache(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store($this->cacheStore);
    }

    private function ipMatchesCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr, 2);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask = -1 << (32 - (int) $bits);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
