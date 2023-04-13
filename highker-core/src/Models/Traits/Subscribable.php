<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models\Traits;

use HighKer\Core\Models\Subscription;
use HighKer\Core\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property Collection $subscriptions
 * @property Collection $subscribers
 */
trait Subscribable
{
    public function isSubscribedBy(Model $user): bool
    {
        if ($user instanceof User) {
            if ($this->relationLoaded('subscribers')) {
                return $this->subscribers->contains($user);
            }

            return (bool) $this->subscribers()->find($user->getKey());
        }

        return false;
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, Subscription::class)->where('subscribable_type', $this->getMorphClass());
    }
}
