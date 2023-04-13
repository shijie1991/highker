<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class UserInfoReview.
 *
 * @property int    id
 * @property int    user_id
 * @property int    type
 * @property string value
 * @property int    created_at
 * @property int    updated_at
 */
class UserInfoReview extends BaseModel
{
    protected $fillable = ['user_id', 'type', 'value'];

    protected static function boot()
    {
        parent::boot();
    }

    public static function createReview($userId, $type, $value)
    {
        UserInfoReview::query()->updateOrCreate(['user_id' => $userId, 'type' => $type], ['value' => $value]);
    }
}
