<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class FeedTopicRelation.
 *
 * @property int    id
 * @property int    feed_id
 * @property int    topic_id
 * @property string created_at
 * @property string updated_at
 */
class FeedTopicRelation extends BaseModel
{
    protected $fillable = [];

    protected static function boot()
    {
        parent::boot();
    }
}
