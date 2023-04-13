<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class FeedImage.
 *
 * @property int    id
 * @property int    user_id
 * @property int    feed_id
 * @property int    width
 * @property int    height
 * @property string status
 * @property string path
 * @property int    created_at
 * @property int    updated_at
 */
class FeedImage extends BaseModel
{
    protected $fillable = ['user_id', 'feed_id', 'width', 'height', 'path'];

    protected $hidden = ['id', 'feed_id', 'user_id', 'status', 'updated_at', 'created_at'];

    protected static function boot()
    {
        parent::boot();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }
}
