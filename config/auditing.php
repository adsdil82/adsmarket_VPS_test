<?php

return [
    'enabled' => env('AUDITING_ENABLED', true),

    'implementation' => OwenIt\Auditing\Models\Audit::class,

    'user' => [
        'morph_prefix' => 'user',
        'guards'       => ['web'],
        'resolver'     => OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    'resolvers' => [
        'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url'        => OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    'events' => [
        'auditing' => OwenIt\Auditing\Events\Auditing::class,
        'audited'  => OwenIt\Auditing\Events\Audited::class,
    ],

    'strict'   => false,
    'excludes' => [],
    'includes' => [],
    'threshold' => 0,
    'console'  => false,

    'queue' => [
        'enable'     => false,
        'connection' => null,
        'queue'      => null,
        'delay'      => null,
    ],

    'drivers' => [
        'database' => [
            'table'      => 'audits',
            'connection' => null,
        ],
    ],

    'driver' => 'database',

    'audit_timestamps' => false,
];
