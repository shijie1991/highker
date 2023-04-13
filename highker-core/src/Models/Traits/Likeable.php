<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models\Traits;

use HighKer\Core\Models\Like;
use HighKer\Core\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property Collection $likers
 */
trait Likeable
{
    public function isLikedBy(Model $user): bool
    {
        if (is_a($user, User::class)) {
            if ($this->relationLoaded('likers')) {
                return $this->likers->contains($user);
            }

            return $this->likers()->where('user_id', $user->getKey())->exists();
        }

        return false;
    }

    /**
     * @return BelongsToMany
     */
    public function likers()
    {
        return $this->belongsToMany(
            User::class,
            Like::class,
            'likeable_id',
            'user_id'
        )->where('likeable_type', $this->getMorphClass());
    }
}
