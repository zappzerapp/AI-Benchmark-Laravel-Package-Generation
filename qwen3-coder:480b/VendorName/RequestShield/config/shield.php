<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blocked IPs
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that should be blocked by the shield.
    | You can use specific IP addresses or CIDR notation for ranges.
    |
    */
    'blocked_ips' => [
        // '192.168.1.1',
        // '10.0.0.0/8',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked User Agents
    |--------------------------------------------------------------------------
    |
    | List of user agents that should be blocked by the shield.
    | These checks are case-insensitive partial matches.
    |
    */
    'blocked_user_agents' => [
        // 'malicious-bot',
        // 'scammer-agent',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Type
    |--------------------------------------------------------------------------
    |
    | Determine how blocked requests should be handled:
    | - 'exception': Throw a 403 Forbidden exception
    | - 'view': Return a 403 view (requires views/shield-403.blade.php)
    |
    */
    'response_type' => 'exception',

    /*
    |--------------------------------------------------------------------------
    | Whitelisted IPs
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that should always be allowed, even if they match
    | blocked patterns. Useful for allowing internal services.
    |
    */
    'whitelisted_ips' => [
        // '127.0.0.1',
        // '192.168.1.100',
    ],
];