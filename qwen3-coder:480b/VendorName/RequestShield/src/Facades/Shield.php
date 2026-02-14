<?php

namespace VendorName\RequestShield\Facades;

use Illuminate\Support\Facades\Facade;

class Shield extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'shield';
    }
}