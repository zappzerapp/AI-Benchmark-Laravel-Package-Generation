<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

final class ShieldService
{
    /** @var list<string> */
    private readonly array $blockedIps;

    /** @var list<string> */
    private readonly array $blockedUserAgents;

    private int $blockedCount = 0;

    /**
     * @param  list<string>  $blockedIps
     * @param  list<string>  $blockedUserAgents
     */
    public function __construct(
        array $blockedIps = [],
        array $blockedUserAgents = [],
    ) {
        $this->blockedIps = array_values($blockedIps);
        $this->blockedUserAgents = array_map('strtolower', array_values($blockedUserAgents));
    }

    public function isIpBlocked(string $ip): bool
    {
        return in_array($ip, $this->blockedIps, strict: true);
    }

    public function isUserAgentBlocked(string $userAgent): bool
    {
        $lowerUserAgent = strtolower($userAgent);

        foreach ($this->blockedUserAgents as $blocked) {
            if (str_contains($lowerUserAgent, $blocked)) {
                return true;
            }
        }

        return false;
    }

    public function shouldBlock(string $ip, string $userAgent): bool
    {
        return $this->isIpBlocked($ip) || $this->isUserAgentBlocked($userAgent);
    }

    public function recordBlocked(): void
    {
        $this->blockedCount++;
    }

    public function getBlockedCount(): int
    {
        return $this->blockedCount;
    }

    public function resetBlockedCount(): void
    {
        $this->blockedCount = 0;
    }
}
