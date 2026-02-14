<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Blocked IP Addresses
    |--------------------------------------------------------------------------
    |
    | Requests from these IP addresses will be rejected with a 403 response.
    | Supports exact matches and CIDR notation (e.g. '192.168.1.0/24').
    |
    */

    'blocked_ips' => [
        // '192.168.1.100',
        // '10.0.0.0/8',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked User-Agent Patterns
    |--------------------------------------------------------------------------
    |
    | Requests whose User-Agent header matches any of these patterns (case-
    | insensitive substring match) will be rejected with a 403 response.
    |
    */

    'blocked_user_agents' => [
        // 'BadBot',
        // 'curl',
        // 'scrapy',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom 403 View
    |--------------------------------------------------------------------------
    |
    | When set to a view name, the middleware renders this view instead of
    | throwing an HttpException. Set to null to throw the exception.
    |
    */

    'forbidden_view' => null,

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | When enabled, every blocked request is logged through Laravel's logger
    | and counted in the cache for the stats command.
    |
    */

    'log_blocked_requests' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | The cache store used for tracking blocked-request statistics.
    | Set to null to use the application's default cache store.
    |
    */

    'cache_store' => null,

];
