<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use VendorName\RequestShield\RequestShieldServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            RequestShieldServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Shield' => \VendorName\RequestShield\Facades\Shield::class,
        ];
    }
}
