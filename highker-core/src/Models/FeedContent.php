<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class FeedContent.
 *
 * @property int    id
 * @property int    feed_id
 * @property string text
 * @property int    created_at
 * @property int    updated_at
 */
class FeedContent extends BaseModel
{
    protected $fillable = ['feed_id', 'text'];

    protected $hidden = ['id', 'created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($content) {
            // 敏感词替换
            if ($content->text) {
                $sensitiveKeywordFilter = app('sensitiveKeywordFilter');
                $content->text = $sensitiveKeywordFilter->replace($content->text);
            }
        });
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }
}
