<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use VendorName\RequestShield\ShieldService;

/**
 * @method static bool isBlocked(Request $request)
 * @method static bool isIpBlocked(string $ip)
 * @method static bool isUserAgentBlocked(string $userAgent)
 * @method static void recordBlocked(Request $request)
 * @method static int  blockedTodayCount()
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
