<?php

namespace VendorName\RequestShield;

use Illuminate\Support\ServiceProvider;
use VendorName\RequestShield\Commands\ShieldStatsCommand;
use VendorName\RequestShield\ShieldService;

class RequestShieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/shield.php' => config_path('shield.php'),
        ], 'shield-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ShieldStatsCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shield.php', 'shield');

        $this->app->singleton('shield', function ($app) {
            return new ShieldService(
                config('shield.blocked_ips', []),
                config('shield.blocked_user_agents', [])
            );
        });
    }
}