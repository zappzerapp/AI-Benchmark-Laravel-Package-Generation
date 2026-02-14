<?php

declare(strict_types=1);

return [
    'blocked_ips' => [
        '192.168.1.100',
        '10.0.0.50',
    ],

    'blocked_user_agents' => [
        'BadBot',
        'MaliciousBot',
        'curl',
        'wget',
    ],

    'enable_logging' => true,

    'return_view' => true,
];
