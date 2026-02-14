<?php

namespace VendorName\RequestShield;

readonly class ShieldService
{
    /**
     * @param array $config
     */
    public function __construct(private array $config)
    {
    }

    /**
     * Check if an IP is blocked
     *
     * @param string $ip
     * @return bool
     */
    public function isIpBlocked(string $ip): bool
    {
        // Check if IP is whitelisted
        if (in_array($ip, $this->config['whitelisted_ips'])) {
            return false;
        }

        // Check exact IP matches
        if (in_array($ip, $this->config['blocked_ips'])) {
            return true;
        }

        // Check CIDR ranges
        foreach ($this->config['blocked_ips'] as $blockedIp) {
            if (str_contains($blockedIp, '/')) {
                if ($this->ipInRange($ip, $blockedIp)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a user agent is blocked
     *
     * @param string $userAgent
     * @return bool
     */
    public function isUserAgentBlocked(string $userAgent): bool
    {
        $userAgent = strtolower($userAgent);

        foreach ($this->config['blocked_user_agents'] as $blockedAgent) {
            if (str_contains($userAgent, strtolower($blockedAgent))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP is in a CIDR range
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if (!str_contains($range, '/')) {
            return false;
        }

        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - (int)$bits);
        $subnet &= $mask;
        return ($ip & $mask) === $subnet;
    }

    /**
     * Get the response type from config
     *
     * @return string
     */
    public function getResponseType(): string
    {
        return $this->config['response_type'] ?? 'exception';
    }
}