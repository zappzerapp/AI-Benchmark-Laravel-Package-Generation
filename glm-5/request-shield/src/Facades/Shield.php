<?php

namespace VendorName\RequestShield\Facades;

use Illuminate\Support\Facades\Facade;
use VendorName\RequestShield\ShieldService;

/**
 * @method static bool shouldBlock(\Illuminate\Http\Request $request)
 * @method static bool isWhitelisted(?string $ip, ?string $userAgent)
 * @method static bool isBlockedIp(?string $ip)
 * @method static bool isBlockedUserAgent(?string $userAgent)
 * @method static void logBlockedRequest(\Illuminate\Http\Request $request)
 * @method static void recordBlock()
 * @method static array getDailyStats()
 *
 * @see ShieldService
 */
final class Shield extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ShieldService::class;
    }
}