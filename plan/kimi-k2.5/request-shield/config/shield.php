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
