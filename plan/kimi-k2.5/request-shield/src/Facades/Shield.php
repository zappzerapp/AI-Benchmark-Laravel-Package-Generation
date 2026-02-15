<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Facades;

use Illuminate\Support\Facades\Facade;
use VendorName\RequestShield\ShieldService;

/**
 * @method static bool isIpBlocked(string $ip)
 * @method static bool isUserAgentBlocked(string $userAgent)
 * @method static bool shouldBlock(string $ip, string $userAgent)
 * @method static void recordBlocked()
 * @method static int getBlockedCount()
 * @method static void resetBlockedCount()
 *
 * @see \VendorName\RequestShield\ShieldService
 */
final class Shield extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ShieldService::class;
    }
}
