<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

use Illuminate\Support\ServiceProvider;

final class RequestShieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/shield.php',
            'shield'
        );

        $this->app->singleton(ShieldService::class, static function ($app) {
            $config = $app['config']['shield'] ?? [];

            return new ShieldService(
                blockedIps: $config['blocked_ips'] ?? [],
                blockedUserAgents: $config['blocked_user_agents'] ?? [],
                enableLogging: $config['enable_logging'] ?? true,
                returnView: $config['return_view'] ?? true
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/shield.php' => config_path('shield.php'),
        ], 'request-shield-config');

        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'request-shield'
        );
    }
}
