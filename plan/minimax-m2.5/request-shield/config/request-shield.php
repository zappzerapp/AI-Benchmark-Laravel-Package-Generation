<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blocked IP Addresses
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that should be blocked from making requests.
    | Supports CIDR notation (e.g., '192.168.1.0/24') and exact matches.
    |
    */
    'blocked_ips' => [
        // Example: '192.168.1.100',
        // Example: '10.0.0.0/24',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked User-Agents
    |--------------------------------------------------------------------------
    |
    | List of User-Agent strings that should be blocked.
    | Partial matching is used (contains).
    |
    */
    'blocked_user_agents' => [
        // Example: 'curl',
        // Example: 'python-requests',
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable Shield
    |--------------------------------------------------------------------------
    |
    | Toggle the shield on/off. When disabled, all requests pass through.
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Response View
    |--------------------------------------------------------------------------
    |
    | The view to render when a request is blocked.
    |
    */
    'response_view' => 'request-shield::blocked',

    /*
    |--------------------------------------------------------------------------
    | Response Status Code
    |--------------------------------------------------------------------------
    |
    | HTTP status code to return when blocking a request.
    |
    */
    'response_status' => 403,
];
