<?php

namespace VendorName\RequestShield;

use Illuminate\Support\ServiceProvider;
use VendorName\RequestShield\Commands\ShieldStatsCommand;
use VendorName\RequestShield\Middleware\ProtectRequest;

class RequestShieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/shield.php' => config_path('shield.php'),
        ], 'shield-config');

        // Register the middleware
        $this->app['router']->aliasMiddleware('shield', ProtectRequest::class);

        // Register the command if we are running in the console
        if ($this->app->runningInConsole()) {
            $this->commands([
                ShieldStatsCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/shield.php', 'shield');

        // Register the service
        $this->app->singleton('shield', function ($app) {
            return new ShieldService(config('shield'));
        });
    }
}