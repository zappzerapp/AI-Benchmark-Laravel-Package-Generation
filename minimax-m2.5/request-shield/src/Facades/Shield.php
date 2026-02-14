<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use VendorName\RequestShield\ShieldService;

/**
 * @method static bool shouldBlock(Request $request)
 * @method static bool isIpBlocked(Request $request)
 * @method static bool isUserAgentBlocked(Request $request)
 * @method static int getBlockedIpsCountToday()
 * @method static int getBlockedUserAgentsCountToday()
 * @method static int getTotalBlockedCountToday()
 * @method static array<string> getBlockedIps()
 * @method static array<string> getBlockedUserAgents()
 * @method static bool isLoggingEnabled()
 * @method static bool shouldReturnView()
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
