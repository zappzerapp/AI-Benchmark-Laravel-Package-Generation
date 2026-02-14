<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blocked IP Addresses
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that should be blocked from accessing the application.
    | Supports both single IPs and CIDR notation for IP ranges.
    |
    */
    'blocked_ips' => [
        '192.168.1.100',
        '10.0.0.50',
        // '192.168.0.0/24', // CIDR notation for IP ranges
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked User Agents
    |--------------------------------------------------------------------------
    |
    | List of user agent patterns (regex) that should be blocked.
    | Common malicious bot patterns are included by default.
    |
    */
    'blocked_user_agents' => [
        'MJ12bot',
        'AhrefsBot',
        'SemrushBot',
        'DotBot',
        'PetalBot',
        '/^python-requests/i',
        '/^curl/i',
        '/^wget/i',
        '/^Go-http-client/i',
        '/^Java/i',
        '/^libwww-perl/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Whitelisted IP Addresses
    |--------------------------------------------------------------------------
    |
    | IP addresses that bypass all blocking rules (e.g., monitoring services,
    | internal services, trusted bots like Google).
    |
    */
    'whitelisted_ips' => [
        '127.0.0.1',
        '::1',
        // Google Bot IP ranges should be considered for whitelisting
    ],

    /*
    |--------------------------------------------------------------------------
    | Whitelisted User Agents
    |--------------------------------------------------------------------------
    |
    | User agent patterns (regex) that bypass blocking rules.
    | Useful for allowing legitimate crawlers.
    |
    */
    'whitelisted_user_agents' => [
        '/Googlebot/i',
        '/bingbot/i',
        '/Slurp/i',
        '/DuckDuckBot/i',
        '/Baiduspider/i',
        '/YandexBot/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Settings
    |--------------------------------------------------------------------------
    |
    | Configure how blocked requests are handled.
    | - redirect: Redirect to a specific URL (null to disable)
    | - view: Custom view to render (null for default 403 view)
    | - message: Custom error message for 403 response
    |
    */
    'response' => [
        'redirect' => null,
        'view' => null,
        'message' => 'Access denied. Your request has been blocked.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    |
    | Configure logging of blocked requests.
    |
    */
    'logging' => [
        'enabled' => true,
        'channel' => env('SHIELD_LOG_CHANNEL', 'daily'),
        'include_headers' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistics Storage
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    |
    | Configure how statistics are stored (for ShieldStatsCommand).
    | Options: 'memory', 'file', 'database'
    |
    */
    'statistics' => [
        'driver' => env('SHIELD_STATS_DRIVER', 'file'),
        'file_path' => storage_path('framework/shield-stats.json'),
    ],
];