<?php

namespace VendorName\RequestShield\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isBlocked(\Illuminate\Http\Request $request)
 * @method static int getBlockedCount()
 */
class Shield extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'shield';
    }
}