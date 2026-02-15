<?php

namespace VendorName\RequestShield\Support;

class ShieldService
{
    private array $config;
    private array $stats = [
        'blocked_ips' => 0,
        'blocked_user_agents' => 0,
    ];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function shouldBlock(string $ip): bool
    {
        $blockedIps = $this->getBlockedIps();

        foreach ($blockedIps as $blocked) {
            if ($this->isIpInCidr($ip, $blocked)) {
                return true;
            }
        }

        return false;
    }

    public function shouldBlockRequest(object $request): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $ip = $request->ip();
        $userAgent = $request->userAgent();

        if ($this->shouldBlock($ip)) {
            return true;
        }

        foreach ($this->getBlockedUserAgents() as $blockedUa) {
            if (stripos($userAgent, $blockedUa) !== false) {
                return true;
            }
        }

        return false;
    }

    public function getBlockedIps(): array
    {
        return $this->config['blocked_ips'] ?? [];
    }

    public function getBlockedUserAgents(): array
    {
        return $this->config['blocked_user_agents'] ?? [];
    }

    public function getResponseStatus(): int
    {
        return $this->config['response_status'] ?? 403;
    }

    public function getResponseView(): string
    {
        return $this->config['response_view'] ?? 'request-shield::blocked';
    }

    public function recordBlock(string $type, string $value): void
    {
        if ($type === 'ip') {
            $this->stats['blocked_ips']++;
        } elseif ($type === 'user-agent') {
            $this->stats['blocked_user_agents']++;
        }
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    private function isIpInCidr(string $ip, string $cidr): bool
    {
        if (strpos($cidr, '/') === false) {
            return $ip === $cidr;
        }

        [$subnet, $mask] = explode('/', $cidr);
        $mask = (int) $mask;

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $maskLong = -1 << (32 - $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}
