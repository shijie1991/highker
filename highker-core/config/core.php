<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

use HighKer\Core\Utils\UrlUtils;

return [
    'admin' => [
        'route' => [
            'domain'     => UrlUtils::getHost(env('ADMIN_URL')),
            'middleware' => ['web', 'admin'],
        ],
    ],
    'api' => [
        'route' => [
            'domain'     => UrlUtils::getHost(env('API_URL')),
            'middleware' => ['api', 'auth:sanctum', 'highker'],
        ],
    ],

    'url' => [
        'static' => 'https://hk-resources.oss-cn-beijing.aliyuncs.com',
        'cdn'    => env('CDN_URL'),
        'api'    => env('API_URL'),
    ],

    // Sae 配置文件
    'sae' => [
        'access_key' => env('SAE_ACCESS_KEY'),
        'secret_key' => env('SAE_SECRET_KEY'),
    ],

    // 缓存配置
    'cache' => env('CACHE_DRIVER', 'file'),

    // 分页设置
    'page_size' => 10,

    // 节流配置 单位为(秒)
    'throttle' => [
        'feed_create'    => 3,
        'comment_create' => 3,
    ],

    // 异步审核时间(秒)
    'check_ttl' => [
        'feed' => 5,
    ],
];
