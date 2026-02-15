<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blocked IP Addresses
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that should be blocked from accessing the application.
    | Supports both IPv4 and IPv6 addresses.
    |
    */
    'blocked_ips' => [
        // '192.168.1.1',
        // '10.0.0.1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked User Agents
    |--------------------------------------------------------------------------
    |
    | List of user agent patterns (regex) that should be blocked.
    | Useful for blocking known malicious bots, scrapers, etc.
    |
    */
    'blocked_user_agents' => [
        // '/^badbot/i',
        // '/scraper/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Mode
    |--------------------------------------------------------------------------
    |
    | How to respond to blocked requests:
    | - 'abort': Returns HTTP 403 Forbidden response
    | - 'view': Renders a custom blocked view
    |
    */
    'response_mode' => 'abort',

    /*
    |--------------------------------------------------------------------------
    | Blocked View
    |--------------------------------------------------------------------------
    |
    | The Blade view to render when a request is blocked and response_mode
    | is set to 'view'.
    |
    */
    'blocked_view' => 'shield::blocked',
];