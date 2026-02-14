<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Facades;

use Illuminate\Support\Facades\Facade;
use VendorName\RequestShield\Contracts\ShieldInterface;

/**
 * @method static bool isBlocked(\Illuminate\Http\Request $request)
 * @method static bool isBlockedIp(string $ip)
 * @method static bool isBlockedUserAgent(?string $userAgent)
 * @method static array getBlockedIps()
 * @method static array getBlockedUserAgents()
 * @method static void addBlockedIp(string $ip)
 * @method static void addBlockedUserAgent(string $userAgent)
 * @method static void incrementBlockedCount()
 * @method static int getTodayBlockedCount()
 *
 * @see \VendorName\RequestShield\Contracts\ShieldInterface
 */
final class Shield extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'shield';
    }
}
