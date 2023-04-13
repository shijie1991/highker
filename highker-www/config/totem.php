<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

return [
    'artisan' => [
        'command_filter' => [
            'corn:*',
            'horizon:*',
        ],
        'whitelist' => true,
    ],

    'broadcasting' => [
        'enabled' => env('TOTEM_BROADCASTING_ENABLED', false),
        'channel' => env('TOTEM_BROADCASTING_CHANNEL', 'task.events'),
    ],
];
