<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'scs'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'scs' => [
            'driver'     => 'scs',
            'access_key' => env('SCS_ACCESS_KEY'),
            'secret_key' => env('SCS_SECRET_KEY'),
            'bucket'     => env('SCS_BUCKET'),
            'domain'     => env('SCS_DOMAIN', 'https://cdn.sinacloud.net/highker'),
        ],

        'aliyun' => [
            'driver'            => 'oss',
            'access_key_id'     => env('OSS_ACCESS_ID'),
            'access_key_secret' => env('OSS_ACCESS_SECRET'),
            'bucket'            => env('OSS_BUCKET'),
            'endpoint'          => env('OSS_ENDPOINT', 'oss-cn-hangzhou.aliyuncs.com'),
            // OSS 外网节点或自定义外部域名
            'cdnDomain' => env('OSS_ALIYUN_CDN_DOMAIN', ''),
            // 如果isCName为true, getUrl会判断cdnDomain是否设定来决定返回的url，如果cdnDomain未设置，则使用endpoint来生成url，否则使用cdn
            'ssl' => env('OSS_ALIYUN_SSL', true),
            // true to use 'https://' and false to use 'http://'. default is false,
            'isCName' => env('OSS_ALIYUN_CNAME', false),
            // 是否使用自定义域名,true: 则Storage.url()会使用自定义的cdn或域名生成文件url， false: 则使用外部节点生成url
            'debug' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
