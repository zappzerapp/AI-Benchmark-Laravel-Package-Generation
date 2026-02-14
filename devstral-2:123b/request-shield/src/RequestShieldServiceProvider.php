<?php

namespace VendorName\RequestShield;

use Illuminate\Support\ServiceProvider;

class RequestShieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/shield.php',
            'shield'
        );

        $this->app->singleton(ShieldService::class, function () {
            return new ShieldService();
        });

        $this->app->bind('shield', function () {
            return $this->app->make(ShieldService::class);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/shield.php' => config_path('shield.php'),
        ], 'shield-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\ShieldStatsCommand::class,
            ]);
        }
    }
}