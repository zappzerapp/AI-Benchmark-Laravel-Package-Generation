<?php

namespace VendorName\RequestShield\Facades;

use Illuminate\Support\Facades\Facade;

class Shield extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \VendorName\RequestShield\ShieldService::class;
    }
}