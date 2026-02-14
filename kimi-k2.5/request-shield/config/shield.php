<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Blockierte IPs
    |--------------------------------------------------------------------------
    |
    | Eine Liste von IP-Adressen oder IP-Bereichen, die blockiert werden sollen.
    | Unterstützt werden:
    | - Einzelne IPs: '192.168.1.1'
    | - Wildcards: '192.168.1.*'
    | - CIDR-Notation: '192.168.1.0/24'
    |
    */
    'blocked_ips' => [
        // Beispiele:
        // '192.168.1.1',
        // '10.0.0.*',
        // '172.16.0.0/12',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blockierte User-Agents
    |--------------------------------------------------------------------------
    |
    | Eine Liste von User-Agent-Strings oder Mustern (mit Wildcards),
    | die blockiert werden sollen.
    |
    */
    'blocked_user_agents' => [
        // Bekannte böswillige Bots
        'BadBot',
        'SpamBot',
        'Crawler/1.0',
        'EvilSpider',
        '*scrapy*',
        '*python-requests*',
        '*curl*',
        '*wget*',
        // Beispiel-Einträge mit Wildcards:
        // '*bot*',
        // '*spider*',
        // '*crawler*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response-Konfiguration
    |--------------------------------------------------------------------------
    |
    | Konfiguration für die Response bei blockierten Requests.
    |
    */
    'response' => [
        // HTTP-Statuscode (403 Forbidden)
        'status_code' => 403,

        // View, das bei einem blockierten Request angezeigt wird
        // Setze auf null, um eine einfache Exception zu werfen
        'view' => 'request-shield::blocked',

        // Nachricht, die angezeigt wird, wenn kein View konfiguriert ist
        'message' => 'Access denied. Your request has been blocked by the shield.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistik-Tracking
    |--------------------------------------------------------------------------
    |
    | Aktiviert das Tracking von blockierten Requests für Statistiken.
    |
    */
    'track_statistics' => true,

    /*
    |--------------------------------------------------------------------------
    | Log-Level
    |--------------------------------------------------------------------------
    |
    | Das Log-Level für blockierte Requests.
    | Mögliche Werte: 'debug', 'info', 'warning', 'error', null
    |
    */
    'log_level' => 'info',
];