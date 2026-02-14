<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

use Illuminate\Support\ServiceProvider;
use VendorName\RequestShield\Commands\ShieldStatsCommand;
use VendorName\RequestShield\Middleware\ProtectRequest;

final class RequestShieldServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/shield.php',
            'shield'
        );

        $this->app->singleton(ShieldService::class, function ($app) {
            $config = $app['config']['shield'];

            return new ShieldService(
                blockedIps: $config['blocked_ips'] ?? [],
                blockedUserAgents: $config['blocked_user_agents'] ?? [],
                enableLogging: $config['enable_logging'] ?? true,
                logChannel: $config['log_channel'] ?? null,
            );
        });

        $this->app->alias(ShieldService::class, 'shield');
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/shield.php' => config_path('shield.php'),
            ], 'shield-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/shield'),
            ], 'shield-views');

            $this->commands([
                ShieldStatsCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'shield');

        // Register middleware
        $this->app['router']->aliasMiddleware('shield', ProtectRequest::class);
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            ShieldService::class,
            'shield',
        ];
    }
}
