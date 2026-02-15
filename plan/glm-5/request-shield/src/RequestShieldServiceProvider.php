<?php

namespace VendorName\RequestShield;

use Illuminate\Support\ServiceProvider;

class RequestShieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/shield.php',
            'shield'
        );

        $this->app->singleton(ShieldService::class, function ($app) {
            return new ShieldService();
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'shield'
        );

        $this->publishes([
            __DIR__ . '/../config/shield.php' => config_path('shield.php'),
        ], 'shield-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/shield'),
        ], 'shield-views');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\ShieldStatsCommand::class,
            ]);
        }
    }
}