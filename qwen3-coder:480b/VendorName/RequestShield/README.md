# RequestShield

A Laravel package to protect your application from malicious bots and unwanted requests.

## Installation

You can install the package via composer:

```bash
composer require vendorname/request-shield
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=shield-config
```

This will create a `shield.php` file in your `config` directory.

## Usage

### Middleware

To protect your routes, add the middleware to your routes or controllers:

```php
// In your routes file
Route::get('/protected', function () {
    return 'Protected content';
})->middleware('shield');

// Or in your controller constructor
public function __construct()
{
    $this->middleware('shield');
}
```

### Facade

You can also use the Shield facade to check if an IP or user agent is blocked:

```php
use VendorName\RequestShield\Facades\Shield;

// Check if an IP is blocked
if (Shield::isIpBlocked('192.168.1.1')) {
    // Handle blocked IP
}

// Check if a user agent is blocked
if (Shield::isUserAgentBlocked('malicious-bot')) {
    // Handle blocked user agent
}
```

### Configuration Options

The configuration file contains the following options:

- `blocked_ips`: Array of IP addresses to block (supports CIDR notation)
- `blocked_user_agents`: Array of user agents to block
- `response_type`: How to handle blocked requests ('exception' or 'view')
- `whitelisted_ips`: Array of IP addresses that are always allowed

## Artisan Command

Check statistics about blocked requests:

```bash
php artisan shield:stats
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.