<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

use Illuminate\Support\ServiceProvider;
use VendorName\RequestShield\Commands\ShieldStatsCommand;
use VendorName\RequestShield\Contracts\ShieldInterface;
use VendorName\RequestShield\Middleware\ProtectRequest;

final readonly class RequestShieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/shield.php',
            'shield'
        );

        $this->app->singleton(ShieldInterface::class, ShieldService::class);
        $this->app->singleton('shield', fn($app) => $app->make(ShieldInterface::class));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/shield.php' => config_path('shield.php'),
        ], 'request-shield-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/request-shield'),
        ], 'request-shield-views');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'request-shield');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ShieldStatsCommand::class,
            ]);
        }

        $this->app['router']->aliasMiddleware('request-shield', ProtectRequest::class);
    }
}