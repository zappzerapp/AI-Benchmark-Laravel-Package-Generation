<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blocked IP Addresses
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that should be blocked from accessing your application.
    | Supports both IPv4 and IPv6 addresses.
    |
    */
    'blocked_ips' => [
        // '192.168.1.100',
        // '10.0.0.5',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked User Agents
    |--------------------------------------------------------------------------
    |
    | List of User-Agent strings that should be blocked.
    | Partial matches are supported (case-insensitive).
    |
    */
    'blocked_user_agents' => [
        'badbot',
        'scraperbot',
        'malicious-crawler',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Type
    |--------------------------------------------------------------------------
    |
    | How to respond when a request is blocked.
    | Options: 'exception' (throws 403), 'view' (returns a view), 'json'
    |
    */
    'response_type' => 'exception',

    /*
    |--------------------------------------------------------------------------
    | Blocked View
    |--------------------------------------------------------------------------
    |
    | The view to display when response_type is 'view'.
    | You can publish and customize this view.
    |
    */
    'blocked_view' => 'shield::blocked',

    /*
    |--------------------------------------------------------------------------
    | Enable Logging
    |--------------------------------------------------------------------------
    |
    | Whether to log blocked requests for statistics.
    |
    */
    'enable_logging' => true,

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | The log channel to use for blocked request logging.
    |
    */
    'log_channel' => 'daily',
];
