<?php

namespace VendorName\RequestShield;

use Illuminate\Http\Request;

class ShieldService
{
    public function __construct(
        private readonly array $blockedIps,
        private readonly array $blockedUserAgents
    ) {}

    public function shouldBlock(Request $request): bool
    {
        return $this->isBlockedIp($request->ip())
            || $this->isBlockedUserAgent($request->userAgent());
    }

    private function isBlockedIp(?string $ip): bool
    {
        if (!$ip) {
            return false;
        }

        return in_array($ip, $this->blockedIps, true);
    }

    private function isBlockedUserAgent(?string $userAgent): bool
    {
        if (!$userAgent) {
            return false;
        }

        foreach ($this->blockedUserAgents as $blockedAgent) {
            if (str_contains(strtolower($userAgent), strtolower($blockedAgent))) {
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
        if (!in_array($ip, $this->blockedIps, true)) {
            $this->blockedIps[] = $ip;
        }
    }

    public function removeBlockedIp(string $ip): void
    {
        $key = array_search($ip, $this->blockedIps, true);
        if ($key !== false) {
            unset($this->blockedIps[$key]);
            $this->blockedIps = array_values($this->blockedIps);
        }
    }

    public function addBlockedUserAgent(string $userAgent): void
    {
        if (!in_array($userAgent, $this->blockedUserAgents, true)) {
            $this->blockedUserAgents[] = $userAgent;
        }
    }

    public function removeBlockedUserAgent(string $userAgent): void
    {
        $key = array_search($userAgent, $this->blockedUserAgents, true);
        if ($key !== false) {
            unset($this->blockedUserAgents[$key]);
            $this->blockedUserAgents = array_values($this->blockedUserAgents);
        }
    }
}