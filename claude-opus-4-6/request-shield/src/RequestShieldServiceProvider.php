<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use VendorName\RequestShield\Commands\ShieldStatsCommand;
use VendorName\RequestShield\Middleware\ProtectRequest;

final class RequestShieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/shield.php', 'shield');

        $this->app->singleton(ShieldService::class, function ($app): ShieldService {
            $config = $app['config']->get('shield', []);

            return new ShieldService(
                blockedIps: $config['blocked_ips'] ?? [],
                blockedUserAgents: $config['blocked_user_agents'] ?? [],
                logBlocked: $config['log_blocked_requests'] ?? true,
                cacheStore: $config['cache_store'] ?? null,
            );
        });
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

        $this->app->make(Kernel::class)->pushMiddleware(ProtectRequest::class);
    }
}
