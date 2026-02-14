<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Facades;

use Illuminate\Support\Facades\Facade;
use VendorName\RequestShield\ShieldService;

/**
 * @method static bool shouldBlock(\Illuminate\Http\Request $request)
 * @method static bool isIpBlocked(?string $ip)
 * @method static bool isUserAgentBlocked(?string $userAgent)
 * @method static void logBlockedRequest(\Illuminate\Http\Request $request, string $reason)
 * @method static int getBlockedCount(?string $date = null)
 * @method static void blockIp(string $ip)
 * @method static void unblockIp(string $ip)
 *
 * @see \VendorName\RequestShield\ShieldService
 */
final class Shield extends Facade
{
    /**
     * Get the registered name of the component
     */
    protected static function getFacadeAccessor(): string
    {
        return ShieldService::class;
    }
}
