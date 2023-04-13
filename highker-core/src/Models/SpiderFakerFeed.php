<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class SpiderFakerFeed.
 */
class SpiderFakerFeed extends BaseModel
{
    protected $fillable = [
        'feed_id',
        'md5_feed_id',
        'user_id',
        'source',
        'status',
        'content',
        'like_count',
        'comment_count',
        'images',
        'source_created_at',
    ];
}
