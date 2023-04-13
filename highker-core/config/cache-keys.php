<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

// redis 缓存Key 配置 格式 模块 =>[功能=>'缓存标识 => 过期时间(秒*分钟*小时*天)];

return [
    'feed' => [
        'list' => [
            'hot:{page}'              => 60 * 10,
            'new:{page}'              => 60 * 10,
            'follow:{user_id}:{page}' => 60 * 10,
        ],
        // 话题相关
        'topic' => [
            'hot:{topic_id}:{page}' => 60 * 10,
            'new:{topic_id}:{page}' => 60 * 10,
        ],
        'like' => [
            // 用户 新点赞集合
            'user-new-like-feed-list:{user_id}' => 60 * 10,
            // 动态 新点赞集合
            'feed-new-like-user-list:{feed_id}' => 60 * 10,
            // 动态 统计 新点赞数量
            'feed-new-counter:{feed_id}' => 60 * 10,
        ],
        'view' => [
            'ip-view:{feed_id}:{ip}'        => 60 * 30,
            'user-view:{feed_id}:{user_id}' => 60 * 30,
        ],
    ],
    'audit' => [
        'type' => [
            'task-type:{task_id}' => 60 * 30,
        ],
        'feed' => [
            'task-id:{task_id}'      => 60 * 30,
            'task-list:{feed_id}'    => 60 * 30,
            'audit-result:{feed_id}' => 60 * 30,
        ],
    ],
    'user' => [
        'online' => [
            'user' => -1,
            'list' => -1,
        ],
        'websocket' => [
            'info:{user_id}' => 60 * 60,
        ],
        'task' => [
            'daily-login:{user_id}'   => 60 * 60 * 24 * 2,
            'daily:{Y-M-D}:{user_id}' => 60 * 60 * 24 * 2,
        ],
        'visit' => [
            'count:{Y-M-D}:{user_id}' => 60 * 60 * 24,
        ],
        // 盲盒
        'box' => [
            'box-data:{user_id}'          => -1,
            'add-count:{Y-M-D}:{user_id}' => 60 * 60 * 24,
            'get-count:{Y-M-D}:{user_id}' => 60 * 60 * 24,
            'get-log:{user_id}'           => -1,
            'list-gender:{gender}'        => -1,
        ],
        'message' => [
            'add-message-count:{Y-M-D}:{user_id}' => 60 * 60 * 24,
        ],
        // 兑换权益
        'exchange' => [
            'info:{Y-M-D}:{user_id}' => 60 * 60 * 24,
        ],
        // 排行榜
        'ranking' => [
            'list:{slug}:{week}' => 60 * 60 * 24 * 8,
        ],
    ],

    'other' => [
        'notices' => [
            'fetch-system-notice:{user_id}' => 60 * 60 * 4,
        ],
        'sensitive-words' => [
            'all' => 60 * 60 * 24 * 10,
        ],
    ],

    'fake' => [
        'feed' => [
            'list' => -1,
        ],
        'user' => [
            'list' => -1,
        ],
    ],
];
