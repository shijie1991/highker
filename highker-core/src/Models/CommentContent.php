<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class CommentContent.
 *
 * @property int    id
 * @property int    comment_id
 * @property string text
 * @property int    created_at
 * @property int    updated_at
 */
class CommentContent extends BaseModel
{
    protected $fillable = ['comment_id', 'text'];

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
}
