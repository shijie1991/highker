<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Observers\SubscriptionObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int   id
 * @property int   user_id
 * @property int   subscribable_type
 * @property int   subscribable_id
 * @property int   created_at
 * @property int   updated_at
 * @property Model $user
 * @property Model $subscriber
 * @property Model $subscribable
 *
 * @method WithType($type)
 */
class Subscription extends BaseModel
{
    protected static function boot()
    {
        parent::boot();

        static::observe(SubscriptionObserver::class);
    }

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->user();
    }

    public function scopeWithType(Builder $query, string $type): Builder
    {
        return $query->where('subscribable_type', app($type)->getMorphClass());
    }
}
