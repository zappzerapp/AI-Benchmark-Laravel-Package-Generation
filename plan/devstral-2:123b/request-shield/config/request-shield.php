<?php

return [
    'blocked_ips' => [
        '192.168.1.100',
        '10.0.0.5',
        '172.16.0.200',
    ],
    
    'blocked_user_agents' => [
        'malicious-bot',
        'evil-crawler',
        'bad-scraper',
    ],
    
    'max_requests_per_minute' => 100,
    
    'log_blocked_requests' => true,
    
    'block_response_message' => 'Access denied. Your request has been blocked.',
    
    'block_response_status_code' => 403,
];