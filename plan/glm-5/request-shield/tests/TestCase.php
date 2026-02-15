<?php

namespace VendorName\RequestShield\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \VendorName\RequestShield\RequestShieldServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('shield.blocked_ips', []);
        $app['config']->set('shield.blocked_user_agents', []);
        $app['config']->set('shield.response_mode', 'abort');
        $app['config']->set('shield.blocked_view', 'shield::blocked');
    }
}