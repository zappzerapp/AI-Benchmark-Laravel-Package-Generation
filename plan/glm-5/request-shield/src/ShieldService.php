<?php

namespace VendorName\RequestShield;

use Illuminate\Support\Facades\Config;

class ShieldService
{
    private int $blockedCount = 0;

    public function isIpBlocked(string $ip): bool
    {
        if (empty($ip)) {
            return false;
        }

        $blockedIps = Config::get('shield.blocked_ips', []);
        return in_array($ip, $blockedIps, true);
    }

    public function isUserAgentBlocked(string $userAgent): bool
    {
        if (empty($userAgent)) {
            return false;
        }

        $blockedPatterns = Config::get('shield.blocked_user_agents', []);

        foreach ($blockedPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    public function shouldBlock(string $ip, string $userAgent): bool
    {
        return $this->isIpBlocked($ip) || $this->isUserAgentBlocked($userAgent);
    }

    public function recordBlocked(string $ip, string $userAgent): void
    {
        $this->blockedCount++;
    }

    public function getStats(): array
    {
        return [
            'total_ips' => count(Config::get('shield.blocked_ips', [])),
            'total_user_agents' => count(Config::get('shield.blocked_user_agents', [])),
            'blocked_count' => $this->blockedCount,
        ];
    }

    public function getResponseMode(): string
    {
        return Config::get('shield.response_mode', 'abort');
    }

    public function getBlockedView(): string
    {
        return Config::get('shield.blocked_view', 'shield::blocked');
    }
}