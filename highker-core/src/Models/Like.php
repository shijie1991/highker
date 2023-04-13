<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Observers\LikeObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Feed.
 *
 * @property int    id
 * @property int    user_id
 * @property string likeable_type
 * @property int    likeable_id
 * @property int    created_at
 * @property int    updated_at
 * @property Model  likeable
 * @property User   user
 */
class Like extends BaseModel
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::observe(LikeObserver::class);
    }

    public function likeable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function liker()
    {
        return $this->user();
    }

    public function scopeWithType(Builder $query, string $type)
    {
        return $query->where('likeable_type', app($type)->getMorphClass());
    }
}
