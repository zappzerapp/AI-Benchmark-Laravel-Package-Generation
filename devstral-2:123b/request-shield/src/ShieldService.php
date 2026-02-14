<?php

namespace VendorName\RequestShield;

use Illuminate\Http\Request;

readonly class ShieldService
{
    public function __construct(
        protected array $config
    ) {
        $this->config = config('shield', []);
    }

    public function isBlocked(Request $request): bool
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        return $this->isIpBlocked($ip) || $this->isUserAgentBlocked($userAgent);
    }

    protected function isIpBlocked(string $ip): bool
    {
        return in_array($ip, $this->config['blocked_ips'] ?? [], true);
    }

    protected function isUserAgentBlocked(string $userAgent): bool
    {
        foreach ($this->config['blocked_user_agents'] ?? [] as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return true;
            }
        }

        return false;
    }

    public function getBlockedCount(): int
    {
        return 0;
    }
}