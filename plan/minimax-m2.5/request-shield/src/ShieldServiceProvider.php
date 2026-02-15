<?php

namespace VendorName\RequestShield;

use Illuminate\Support\ServiceProvider;
use VendorName\RequestShield\Console\ShieldStatsCommand;
use VendorName\RequestShield\Support\ShieldService;

class ShieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/request-shield.php', 'request-shield');

        $this->app->singleton(ShieldService::class, function ($app) {
            $config = $app['config']['request-shield'] ?? [];
            return new ShieldService($config);
        });

        $this->app->singleton(ShieldStatsCommand::class, function ($app) {
            return new ShieldStatsCommand($app->make(ShieldService::class));
        });

        $this->commands([ShieldStatsCommand::class]);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/request-shield.php' => config_path('request-shield.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'request-shield');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/request-shield'),
        ], 'views');
    }
}
