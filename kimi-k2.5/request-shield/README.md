# RequestShield

Ein Laravel-Package zum Schutz vor böswilligen Bots und spezifischen IPs durch Middleware.

## Installation

```bash
composer require vendorname/request-shield
```

## Konfiguration

Veröffentlichen Sie die Konfiguration:

```bash
php artisan vendor:publish --tag=request-shield-config
```

Veröffentlichen Sie die Views (optional):

```bash
php artisan vendor:publish --tag=request-shield-views
```

## Verwendung

### Als Middleware registrieren

Fügen Sie die Middleware zur `bootstrap/app.php` oder zu Ihren Routen hinzu:

```php
// In bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\VendorName\RequestShield\Middleware\ProtectRequest::class);
})

// Oder in routes/web.php
Route::middleware('request-shield')->group(function () {
    // Geschützte Routen
});
```

### Konfiguration

Bearbeiten Sie `config/shield.php`:

```php
return [
    'blocked_ips' => [
        '192.168.1.1',
        '10.0.0.*',       // Wildcards
        '172.16.0.0/24',  // CIDR-Notation
    ],
    
    'blocked_user_agents' => [
        'BadBot',
        '*bot*',          // Wildcards
        'Python-urllib',
    ],
    
    'response' => [
        'status_code' => 403,
        'view' => 'request-shield::blocked',
        'message' => 'Access denied.',
    ],
];
```

### Facade verwenden

```php
use VendorName\RequestShield\Facades\Shield;

// Prüfen ob IP blockiert ist
if (Shield::isBlockedIp($ip)) {
    // ...
}

// Prüfen ob User-Agent blockiert ist
if (Shield::isBlockedUserAgent($userAgent)) {
    // ...
}

// IPs zur Laufzeit hinzufügen
Shield::addBlockedIp('192.168.1.100');
Shield::addBlockedUserAgent('CustomBot');

// Statistiken anzeigen
$count = Shield::getTodayBlockedCount();
```

### Artisan Command

Zeigen Sie Statistiken über blockierte Requests an:

```bash
# Heutige Statistiken
php artisan shield:stats

# Statistiken für gestern
php artisan shield:stats --yesterday

# Statistiken für ein bestimmtes Datum
php artisan shield:stats --date=2024-01-15
```

## Features

- **IP-Blocking**: Einzelne IPs, Wildcards und CIDR-Notation
- **User-Agent-Blocking**: Musterbasierte Blockierung
- **Statistiken**: Tracking der blockierten Requests
- **Konfigurierbare Response**: View oder Exception
- **Laravel Package Discovery**: Automatische Registrierung

## Lizenz

MIT