<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Observers\UserFollowObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserFollow.
 *
 * @property int    id
 * @property int    follower_id
 * @property int    following_id
 * @property string created_at
 * @property string updated_at
 * @property User   follower_user
 * @property User   following_user
 */
class UserFollow extends BasePivot
{
    protected static function boot()
    {
        parent::boot();

        static::observe(UserFollowObserver::class);
    }

    /**
     * 关注的用户.
     */
    public function follower_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id', 'id')->select([
            'id',
            'name',
            'avatar',
            'gender',
            'level',
            'deleted_at',
        ]);
    }

    /**
     * 被关注的用户.
     */
    public function following_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following_id', 'id')->select([
            'id',
            'name',
            'avatar',
            'gender',
            'level',
            'deleted_at',
        ]);
    }
}
