# RequestShield

A Laravel middleware package to block malicious bots and specific IP addresses with modern PHP 8.2+ features.

## Features

- 🛡️ Block requests by IP address
- 🤖 Block requests by User-Agent patterns
- 📊 Track and log blocked requests
- 🎨 Customizable block response (Exception, View, or JSON)
- ⚡ Built with PHP 8.2+ (readonly classes, constructor promotion, typed properties)
- 📈 Statistics command to view blocked requests

## Requirements

- PHP 8.2 or higher
- Laravel 10.x or 11.x

## Installation

Install the package via Composer:

```bash
composer require vendorname/request-shield
```

The package will automatically register itself via Laravel's package discovery.

Publish the configuration file:

```bash
php artisan vendor:publish --tag=shield-config
```

Optionally, publish the views:

```bash
php artisan vendor:publish --tag=shield-views
```

## Configuration

Edit `config/shield.php` to customize your settings:

```php
return [
    'blocked_ips' => [
        '192.168.1.100',
        '10.0.0.5',
    ],

    'blocked_user_agents' => [
        'badbot',
        'scraperbot',
        'malicious-crawler',
    ],

    'response_type' => 'exception', // 'exception', 'view', or 'json'
    'blocked_view' => 'shield::blocked',
    'enable_logging' => true,
    'log_channel' => 'daily',
];
```

## Usage

### Apply Middleware Globally

Add to `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \VendorName\RequestShield\Middleware\ProtectRequest::class,
];
```

### Apply to Route Groups

```php
Route::middleware(['shield'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

### Apply to Specific Routes

```php
Route::get('/api/sensitive', [ApiController::class, 'sensitive'])
    ->middleware('shield');
```

### Using the Facade

```php
use VendorName\RequestShield\Facades\Shield;

// Check if IP is blocked
if (Shield::isIpBlocked('192.168.1.100')) {
    // Handle blocked IP
}

// Check if User-Agent is blocked
if (Shield::isUserAgentBlocked('badbot')) {
    // Handle blocked user agent
}

// Dynamically block an IP
Shield::blockIp('192.168.1.200');

// Unblock an IP
Shield::unblockIp('192.168.1.200');

// Get blocked request count for today
$count = Shield::getBlockedCount();

// Get blocked request count for a specific date
$count = Shield::getBlockedCount('2024-01-15');
```

## Artisan Command

View blocking statistics:

```bash
# Show stats for the last 7 days (default)
php artisan shield:stats

# Show stats for the last 30 days
php artisan shield:stats --last-days=30

# Show stats for a specific date
php artisan shield:stats --date=2024-01-15
```

Example output:

```
🛡️  RequestShield Statistics

+------------+------------------+-------+
| Date       | Blocked Requests | Graph |
+------------+------------------+-------+
| 2024-01-15 | 42               | ████████████████████████████████████████████ |
| 2024-01-14 | 28               | ████████████████████████████ |
+------------+------------------+-------+

Total blocked in last 7 days: 156

Current Configuration:
📍 Blocked IPs: 2
   - 192.168.1.100
   - 10.0.0.5

🤖 Blocked User-Agents: 3
   - badbot
   - scraperbot
   - malicious-crawler

📊 Logging: ✅ Enabled
📝 Response Type: exception
```

## Response Types

### Exception (Default)

Throws a 403 HTTP exception:

```php
'response_type' => 'exception',
```

### View

Returns a customizable Blade view:

```php
'response_type' => 'view',
'blocked_view' => 'shield::blocked',
```

### JSON

Returns a JSON response (ideal for APIs):

```php
'response_type' => 'json',
```

Response format:

```json
{
    "message": "Access Forbidden",
    "reason": "Blocked IP: 192.168.1.100",
    "timestamp": "2024-01-15T10:30:00+00:00"
}
```

## License

This package is open-source software licensed under the MIT license.
