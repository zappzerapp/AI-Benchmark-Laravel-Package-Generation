<?php

namespace VendorName\RequestShield;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use VendorName\RequestShield\Commands\ShieldStatsCommand;
use VendorName\RequestShield\Middleware\ProtectRequest;

final class RequestShieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/shield.php',
            'shield'
        );

        $this->app->singleton(ShieldService::class, fn () => ShieldService::make());

        $this->app->bind('shield', ShieldService::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/shield.php' => config_path('shield.php'),
        ], 'shield-config');

        $this->publishes([
            __DIR__ . '/../views' => resource_path('views/vendor/request-shield'),
        ], 'shield-views');

        $this->loadViewsFrom(__DIR__ . '/../views', 'request-shield');

        $this->registerMiddleware();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ShieldStatsCommand::class,
            ]);
        }
    }

    private function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('shield', ProtectRequest::class);

        if (config('shield.global_middleware', false)) {
            $kernel = $this->app->make(Kernel::class);
            $kernel->pushMiddleware(ProtectRequest::class);
        }
    }
}