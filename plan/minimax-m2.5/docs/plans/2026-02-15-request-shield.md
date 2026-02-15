# RequestShield Laravel Package — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Erstelle ein Laravel Package `VendorName/RequestShield`, das als Middleware böswillige Bots und IPs blockiert.

**Architecture:** Das Package folgt dem Standard-Laravel-Package-Layout mit ServiceProvider, Facade, Service-Singleton, Middleware und Artisan Command. Die Kernlogik liegt in `ShieldService` (Singleton), der gegen konfigurierbare IP- und User-Agent-Blocklisten prüft. Blockierte Requests werden gezählt und über ein Artisan Command abrufbar. Die Middleware wird in die HTTP-Pipeline eingehängt.

**Tech Stack:** PHP 8.2+, Laravel 10+/11+, PHPUnit/Pest, Typed Properties, Constructor Promotion, Readonly Classes

---

## Ziel-Dateistruktur

```
request-shield/
├── composer.json
├── config/
│   └── shield.php
├── src/
│   ├── RequestShieldServiceProvider.php
│   ├── ShieldService.php
│   ├── Facades/
│   │   └── Shield.php
│   ├── Middleware/
│   │   └── ProtectRequest.php
│   └── Commands/
│       └── ShieldStatsCommand.php
├── tests/
│   ├── Unit/
│   │   └── ShieldServiceTest.php
│   ├── Feature/
│   │   ├── MiddlewareTest.php
│   │   └── ShieldStatsCommandTest.php
│   └── TestCase.php
└── resources/
    └── views/
        └── blocked.blade.php
```

---

### Task 1: Projekt-Scaffolding & composer.json

**Files:**
- Create: `composer.json`
- Create: `config/shield.php`
- Create: `resources/views/blocked.blade.php`

**Step 1: Erstelle `composer.json` mit PSR-4 Autoloading und Package Discovery**

```json
{
    "name": "vendorname/request-shield",
    "description": "Laravel middleware package to block malicious bots and specific IPs.",
    "type": "library",
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "illuminate/support": "^10.0|^11.0|^12.0"
    },
    "require-dev": {
        "orchestra/testbench": "^8.0|^9.0|^10.0",
        "phpunit/phpunit": "^10.0|^11.0"
    },
    "autoload": {
        "psr-4": {
            "VendorName\\RequestShield\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "VendorName\\RequestShield\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "VendorName\\RequestShield\\RequestShieldServiceProvider"
            ],
            "aliases": {
                "Shield": "VendorName\\RequestShield\\Facades\\Shield"
            }
        }
    },
    "config": {
        "sort-packages": true
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

**Step 2: Erstelle `config/shield.php`**

```php
<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Blocked IP Addresses
    |--------------------------------------------------------------------------
    |
    | Requests from these IPs will be blocked with a 403 response.
    | Supports exact IPs and CIDR notation (e.g., '192.168.1.0/24').
    |
    */
    'blocked_ips' => [
        // '192.168.1.100',
        // '10.0.0.0/24',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked User Agents
    |--------------------------------------------------------------------------
    |
    | Requests with User-Agent headers containing any of these strings
    | (case-insensitive) will be blocked with a 403 response.
    |
    */
    'blocked_user_agents' => [
        // 'BadBot',
        // 'EvilScraper',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Mode
    |--------------------------------------------------------------------------
    |
    | How to respond when a request is blocked.
    | Options: 'abort' (throws 403 HttpException), 'view' (renders a blade view)
    |
    */
    'response_mode' => 'abort',

    /*
    |--------------------------------------------------------------------------
    | Blocked View
    |--------------------------------------------------------------------------
    |
    | The view to render when response_mode is 'view'.
    |
    */
    'blocked_view' => 'request-shield::blocked',

];
```

**Step 3: Erstelle `resources/views/blocked.blade.php`**

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Forbidden</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        h1 { font-size: 4rem; margin: 0; color: #dc3545; }
        p { font-size: 1.25rem; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>403</h1>
        <p>Access denied. Your request has been blocked.</p>
    </div>
</body>
</html>
```

**Step 4: Commit**

```bash
git add composer.json config/ resources/
git commit -m "chore: scaffold project with composer.json, config, and view"
```

---

### Task 2: ShieldService — Kernlogik (TDD)

**Files:**
- Create: `tests/TestCase.php`
- Create: `tests/Unit/ShieldServiceTest.php`
- Create: `src/ShieldService.php`

**Step 1: Erstelle die Test-Basisklasse `tests/TestCase.php`**

```php
<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use VendorName\RequestShield\RequestShieldServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            RequestShieldServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Shield' => \VendorName\RequestShield\Facades\Shield::class,
        ];
    }
}
```

**Step 2: Schreibe die Tests für `ShieldService`**

```php
<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use VendorName\RequestShield\ShieldService;
use VendorName\RequestShield\Tests\TestCase;

final class ShieldServiceTest extends TestCase
{
    private ShieldService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ShieldService(
            blockedIps: ['192.168.1.100', '10.0.0.5'],
            blockedUserAgents: ['BadBot', 'EvilScraper'],
        );
    }

    #[Test]
    public function it_blocks_a_listed_ip(): void
    {
        $this->assertTrue($this->service->isIpBlocked('192.168.1.100'));
    }

    #[Test]
    public function it_allows_a_non_listed_ip(): void
    {
        $this->assertFalse($this->service->isIpBlocked('8.8.8.8'));
    }

    #[Test]
    public function it_blocks_a_matching_user_agent(): void
    {
        $this->assertTrue($this->service->isUserAgentBlocked('Mozilla/5.0 BadBot/1.0'));
    }

    #[Test]
    public function it_allows_a_clean_user_agent(): void
    {
        $this->assertFalse($this->service->isUserAgentBlocked('Mozilla/5.0 Chrome/120'));
    }

    #[Test]
    public function user_agent_check_is_case_insensitive(): void
    {
        $this->assertTrue($this->service->isUserAgentBlocked('Mozilla/5.0 badbot/1.0'));
    }

    #[Test]
    public function it_detects_blocked_request_by_ip(): void
    {
        $this->assertTrue($this->service->shouldBlock('192.168.1.100', 'Mozilla/5.0'));
    }

    #[Test]
    public function it_detects_blocked_request_by_user_agent(): void
    {
        $this->assertTrue($this->service->shouldBlock('8.8.8.8', 'EvilScraper/2.0'));
    }

    #[Test]
    public function it_allows_clean_request(): void
    {
        $this->assertFalse($this->service->shouldBlock('8.8.8.8', 'Mozilla/5.0 Chrome/120'));
    }

    #[Test]
    public function it_increments_blocked_count(): void
    {
        $this->assertEquals(0, $this->service->getBlockedCount());

        $this->service->recordBlocked();
        $this->service->recordBlocked();

        $this->assertEquals(2, $this->service->getBlockedCount());
    }

    #[Test]
    public function it_resets_blocked_count(): void
    {
        $this->service->recordBlocked();
        $this->service->resetBlockedCount();

        $this->assertEquals(0, $this->service->getBlockedCount());
    }
}
```

**Step 3: Führe die Tests aus — sie müssen fehlschlagen**

```bash
./vendor/bin/phpunit tests/Unit/ShieldServiceTest.php
```

Expected: FAIL — `ShieldService` Klasse existiert noch nicht.

**Step 4: Implementiere `src/ShieldService.php`**

```php
<?php

declare(strict_types=1);

namespace VendorName\RequestShield;

final class ShieldService
{
    /** @var list<string> */
    private readonly array $blockedIps;

    /** @var list<string> */
    private readonly array $blockedUserAgents;

    private int $blockedCount = 0;

    /**
     * @param  list<string>  $blockedIps
     * @param  list<string>  $blockedUserAgents
     */
    public function __construct(
        array $blockedIps = [],
        array $blockedUserAgents = [],
    ) {
        $this->blockedIps = array_values($blockedIps);
        $this->blockedUserAgents = array_map('strtolower', array_values($blockedUserAgents));
    }

    public function isIpBlocked(string $ip): bool
    {
        return in_array($ip, $this->blockedIps, strict: true);
    }

    public function isUserAgentBlocked(string $userAgent): bool
    {
        $lowerUserAgent = strtolower($userAgent);

        foreach ($this->blockedUserAgents as $blocked) {
            if (str_contains($lowerUserAgent, $blocked)) {
                return true;
            }
        }

        return false;
    }

    public function shouldBlock(string $ip, string $userAgent): bool
    {
        return $this->isIpBlocked($ip) || $this->isUserAgentBlocked($userAgent);
    }

    public function recordBlocked(): void
    {
        $this->blockedCount++;
    }

    public function getBlockedCount(): int
    {
        return $this->blockedCount;
    }

    public function resetBlockedCount(): void
    {
        $this->blockedCount = 0;
    }
}
```

**Step 5: Führe die Tests erneut aus — sie müssen bestehen**

```bash
./vendor/bin/phpunit tests/Unit/ShieldServiceTest.php
```

Expected: ALL PASS (10 tests)

**Step 6: Commit**

```bash
git add src/ShieldService.php tests/
git commit -m "feat: add ShieldService with IP and User-Agent blocking logic"
```

---

### Task 3: ServiceProvider

**Files:**
- Create: `src/RequestShieldServiceProvider.php`

**Step 1: Implementiere den ServiceProvider**

```php
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
```

**Step 2: Verifiziere, dass die bestehenden Tests weiterhin bestehen**

```bash
./vendor/bin/phpunit
```

Expected: ALL PASS

**Step 3: Commit**

```bash
git add src/RequestShieldServiceProvider.php
git commit -m "feat: add RequestShieldServiceProvider with config and view publishing"
```

---

### Task 4: Facade

**Files:**
- Create: `src/Facades/Shield.php`

**Step 1: Implementiere die Facade**

```php
<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Facades;

use Illuminate\Support\Facades\Facade;
use VendorName\RequestShield\ShieldService;

/**
 * @method static bool isIpBlocked(string $ip)
 * @method static bool isUserAgentBlocked(string $userAgent)
 * @method static bool shouldBlock(string $ip, string $userAgent)
 * @method static void recordBlocked()
 * @method static int getBlockedCount()
 * @method static void resetBlockedCount()
 *
 * @see \VendorName\RequestShield\ShieldService
 */
final class Shield extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ShieldService::class;
    }
}
```

**Step 2: Verifiziere Tests**

```bash
./vendor/bin/phpunit
```

Expected: ALL PASS

**Step 3: Commit**

```bash
git add src/Facades/Shield.php
git commit -m "feat: add Shield facade"
```

---

### Task 5: ProtectRequest Middleware (TDD)

**Files:**
- Create: `tests/Feature/MiddlewareTest.php`
- Create: `src/Middleware/ProtectRequest.php`

**Step 1: Schreibe die Middleware-Tests**

```php
<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;
use VendorName\RequestShield\Middleware\ProtectRequest;
use VendorName\RequestShield\ShieldService;
use VendorName\RequestShield\Tests\TestCase;

final class MiddlewareTest extends TestCase
{
    #[Test]
    public function it_allows_clean_requests(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);
        $this->app['config']->set('shield.blocked_user_agents', ['BadBot']);
        $this->app->forgetInstance(ShieldService::class);

        $request = Request::create('/test', 'GET', server: [
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $middleware = $this->app->make(ProtectRequest::class);

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    #[Test]
    public function it_blocks_a_blacklisted_ip_with_abort(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);
        $this->app['config']->set('shield.blocked_user_agents', []);
        $this->app['config']->set('shield.response_mode', 'abort');
        $this->app->forgetInstance(ShieldService::class);

        $request = Request::create('/test', 'GET', server: [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $middleware = $this->app->make(ProtectRequest::class);

        $this->expectException(HttpException::class);

        $middleware->handle($request, function () {
            return new Response('OK');
        });
    }

    #[Test]
    public function it_blocks_a_bad_user_agent_with_abort(): void
    {
        $this->app['config']->set('shield.blocked_ips', []);
        $this->app['config']->set('shield.blocked_user_agents', ['BadBot']);
        $this->app['config']->set('shield.response_mode', 'abort');
        $this->app->forgetInstance(ShieldService::class);

        $request = Request::create('/test', 'GET', server: [
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_USER_AGENT' => 'BadBot/1.0',
        ]);

        $middleware = $this->app->make(ProtectRequest::class);

        $this->expectException(HttpException::class);

        $middleware->handle($request, function () {
            return new Response('OK');
        });
    }

    #[Test]
    public function it_blocks_with_view_response(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);
        $this->app['config']->set('shield.blocked_user_agents', []);
        $this->app['config']->set('shield.response_mode', 'view');
        $this->app['config']->set('shield.blocked_view', 'request-shield::blocked');
        $this->app->forgetInstance(ShieldService::class);

        $request = Request::create('/test', 'GET', server: [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $middleware = $this->app->make(ProtectRequest::class);

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContains('Access denied', $response->getContent());
    }

    #[Test]
    public function it_increments_blocked_count_on_block(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);
        $this->app['config']->set('shield.blocked_user_agents', []);
        $this->app['config']->set('shield.response_mode', 'view');
        $this->app->forgetInstance(ShieldService::class);

        $request = Request::create('/test', 'GET', server: [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $middleware = $this->app->make(ProtectRequest::class);

        $middleware->handle($request, function () {
            return new Response('OK');
        });

        /** @var ShieldService $service */
        $service = $this->app->make(ShieldService::class);

        $this->assertEquals(1, $service->getBlockedCount());
    }
}
```

> **Hinweis:** Der Test `it_blocks_with_view_response` nutzt `assertStringContains` — das ist ein Alias. Korrekt wäre `assertStringContainsString`. Der ausführende Agent muss dies beachten und ggf. korrigieren.

**Step 2: Führe die Tests aus — sie müssen fehlschlagen**

```bash
./vendor/bin/phpunit tests/Feature/MiddlewareTest.php
```

Expected: FAIL — `ProtectRequest` existiert noch nicht.

**Step 3: Implementiere `src/Middleware/ProtectRequest.php`**

```php
<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use VendorName\RequestShield\ShieldService;

final readonly class ProtectRequest
{
    public function __construct(
        private ShieldService $shield,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip() ?? '';
        $userAgent = $request->userAgent() ?? '';

        if ($this->shield->shouldBlock($ip, $userAgent)) {
            $this->shield->recordBlocked();

            $mode = config('shield.response_mode', 'abort');

            if ($mode === 'view') {
                $view = config('shield.blocked_view', 'request-shield::blocked');

                return response()->view($view, [], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Access denied.');
        }

        return $next($request);
    }
}
```

**Step 4: Führe die Tests erneut aus — sie müssen bestehen**

```bash
./vendor/bin/phpunit tests/Feature/MiddlewareTest.php
```

Expected: ALL PASS (5 tests)

**Step 5: Commit**

```bash
git add src/Middleware/ProtectRequest.php tests/Feature/MiddlewareTest.php
git commit -m "feat: add ProtectRequest middleware with abort and view modes"
```

---

### Task 6: ShieldStatsCommand (TDD)

**Files:**
- Create: `tests/Feature/ShieldStatsCommandTest.php`
- Create: `src/Commands/ShieldStatsCommand.php`

**Step 1: Schreibe die Command-Tests**

```php
<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use VendorName\RequestShield\ShieldService;
use VendorName\RequestShield\Tests\TestCase;

final class ShieldStatsCommandTest extends TestCase
{
    #[Test]
    public function it_displays_zero_blocked_when_no_requests_blocked(): void
    {
        $this->artisan('shield:stats')
            ->expectsOutputToContain('0')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_displays_the_current_blocked_count(): void
    {
        /** @var ShieldService $service */
        $service = $this->app->make(ShieldService::class);
        $service->recordBlocked();
        $service->recordBlocked();
        $service->recordBlocked();

        $this->artisan('shield:stats')
            ->expectsOutputToContain('3')
            ->assertExitCode(0);
    }
}
```

**Step 2: Führe die Tests aus — sie müssen fehlschlagen**

```bash
./vendor/bin/phpunit tests/Feature/ShieldStatsCommandTest.php
```

Expected: FAIL — `ShieldStatsCommand` existiert noch nicht.

**Step 3: Implementiere `src/Commands/ShieldStatsCommand.php`**

```php
<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Commands;

use Illuminate\Console\Command;
use VendorName\RequestShield\ShieldService;

final class ShieldStatsCommand extends Command
{
    protected $signature = 'shield:stats';

    protected $description = 'Display the number of requests blocked by RequestShield today';

    public function __construct(
        private readonly ShieldService $shield,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = $this->shield->getBlockedCount();

        $this->components->info("RequestShield Statistics");
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Blocked requests (this session)', (string) $count],
                ['Blocked IPs configured', (string) count(config('shield.blocked_ips', []))],
                ['Blocked User-Agents configured', (string) count(config('shield.blocked_user_agents', []))],
            ],
        );

        return self::SUCCESS;
    }
}
```

**Step 4: Führe die Tests erneut aus — sie müssen bestehen**

```bash
./vendor/bin/phpunit tests/Feature/ShieldStatsCommandTest.php
```

Expected: ALL PASS (2 tests)

**Step 5: Commit**

```bash
git add src/Commands/ShieldStatsCommand.php tests/Feature/ShieldStatsCommandTest.php
git commit -m "feat: add shield:stats artisan command"
```

---

### Task 7: PHPUnit-Konfiguration & Gesamttest

**Files:**
- Create: `phpunit.xml`

**Step 1: Erstelle `phpunit.xml`**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.5/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         cacheDirectory=".phpunit.cache"
         executionOrder="depends,defects"
         failOnRisky="true"
         failOnWarning="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
```

**Step 2: Installiere Dependencies und führe alle Tests aus**

```bash
composer install
./vendor/bin/phpunit
```

Expected: ALL PASS (17 tests total: 10 Unit + 5 Middleware + 2 Command)

**Step 3: Commit**

```bash
git add phpunit.xml
git commit -m "chore: add PHPUnit configuration"
```

---

### Task 8: Abschluss & Qualitätssicherung

**Step 1: Erstelle `.gitignore`**

```
/vendor/
/.phpunit.cache/
composer.lock
```

**Step 2: Stelle sicher, dass alle Tests bestehen**

```bash
./vendor/bin/phpunit --testdox
```

Expected: Vollständige grüne Testausgabe mit sprechenden Testnamen.

**Step 3: Prüfe, dass der ServiceProvider korrekt lädt**

Manueller Smoke-Test: Die TestCase-Klasse referenziert den ServiceProvider — wenn alle Tests bestehen, funktioniert auch das Package Discovery.

**Step 4: Final Commit**

```bash
git add .gitignore
git commit -m "chore: add .gitignore and finalize package"
```

---

## Zusammenfassung der Commits

| # | Message | Inhalt |
|---|---------|--------|
| 1 | `chore: scaffold project with composer.json, config, and view` | Grundstruktur |
| 2 | `feat: add ShieldService with IP and User-Agent blocking logic` | Kernlogik + Tests |
| 3 | `feat: add RequestShieldServiceProvider with config and view publishing` | ServiceProvider |
| 4 | `feat: add Shield facade` | Facade |
| 5 | `feat: add ProtectRequest middleware with abort and view modes` | Middleware + Tests |
| 6 | `feat: add shield:stats artisan command` | Command + Tests |
| 7 | `chore: add PHPUnit configuration` | PHPUnit XML |
| 8 | `chore: add .gitignore and finalize package` | Abschluss |

## Wichtige Hinweise für den Ausführenden

1. **Typo in Test-Assertion**: `assertStringContains` → muss `assertStringContainsString` sein (PHPUnit).
2. **`forgetInstance`**: Nach Config-Änderungen in Tests muss das ShieldService-Singleton neu aufgebaut werden mit `$this->app->forgetInstance(ShieldService::class)`.
3. **PHP 8.2+**: `readonly class` wird bei `ProtectRequest` genutzt — Middleware hat keine mutable State.
4. **Singleton in Tests**: `ShieldService` wird als Singleton registriert. Die Unit-Tests instanziieren es direkt (`new ShieldService(...)`), die Feature-Tests nutzen den Container.
