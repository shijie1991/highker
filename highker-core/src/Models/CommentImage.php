<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class FeedCommentImage.
 *
 * @property int    id
 * @property int    user_id
 * @property int    comment_id
 * @property int    width
 * @property int    height
 * @property string path
 * @property int    created_at
 * @property int    updated_at
 */
class CommentImage extends BaseModel
{
    protected $fillable = ['user_id', 'comment_id', 'width', 'height', 'path'];

    protected static function boot()
    {
        parent::boot();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
