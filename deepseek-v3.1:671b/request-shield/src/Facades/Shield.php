<?php

namespace VendorName\RequestShield\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool shouldBlock(Request $request)
 * @method static array getBlockedIps()
 * @method static array getBlockedUserAgents()
 * @method static void addBlockedIp(string $ip)
 * @method static void removeBlockedIp(string $ip)
 * @method static void addBlockedUserAgent(string $userAgent)
 * @method static void removeBlockedUserAgent(string $userAgent)
 */
class Shield extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'shield';
    }
}