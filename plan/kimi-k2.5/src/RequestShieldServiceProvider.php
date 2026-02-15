<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

use Illuminate\Support\ServiceProvider;
use VendorName\RequestShield\Commands\ShieldStatsCommand;

final class RequestShieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/shield.php', 'shield');

        $this->app->singleton(ShieldService::class, function ($app): ShieldService {
            /** @var array{blocked_ips: list<string>, blocked_user_agents: list<string>} $config */
            $config = $app['config']->get('shield');

            return new ShieldService(
                blockedIps: $config['blocked_ips'] ?? [],
                blockedUserAgents: $config['blocked_user_agents'] ?? [],
            );
        });

        $this->app->alias(ShieldService::class, 'shield');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/shield.php' => config_path('shield.php'),
            ], 'shield-config');

            $this->commands([
                ShieldStatsCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'request-shield');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/request-shield'),
        ], 'shield-views');
    }
}
