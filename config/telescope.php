<?php

return [
    'default' => env('TELESCOPE_ENABLED', true) ? 'default' : null,

    'storage' => [
        'default' => [
            'driver' => 'database',
            'connection' => env('TELESCOPE_DB_CONNECTION'),
        ],
    ],

    'in_app_paths' => [
        base_path(),
    ],

    'ignore_paths' => [
        'nova-api',
    ],

    'ignore_commands' => [
        'migrate',
        'tinker',
    ],

    'gate' => fn ($request) => true,
];
