<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models\Traits;

use HighKer\Core\Models\User;
use HighKer\Core\Models\UserFollow;
use HighKer\Core\Models\UserVisit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * @property $this followings
 * @property $this followers
 */
trait FollowTraits
{
    /**
     * @param $user
     */
    public function follow($user)
    {
        return $this->followings()->attach($user);
    }

    /**
     * @param $user
     */
    public function unfollow($user)
    {
        $this->followings()->detach($user);
    }

    /**
     * @param $user
     */
    public function isFollowing($user): bool
    {
        if ($user instanceof Model) {
            $user = $user->getKey();
        }

        if ($this->relationLoaded('followings')) {
            return $this->followings->contains($user);
        }

        return $this->followings()->where($this->getQualifiedKeyName(), $user)->exists();
    }

    /**
     * @param $user
     */
    public function isFollowedBy($user): bool
    {
        if ($user instanceof Model) {
            $user = $user->getKey();
        }

        if ($this->relationLoaded('followers')) {
            return $this->followers->contains($user);
        }

        return $this->followers()->where($this->getQualifiedKeyName(), $user)->exists();
    }

    /**
     * @param $user
     */
    public function areFollowingEachOther($user): bool
    {
        return $this->isFollowing($user) && $this->isFollowedBy($user);
    }

    /**
     * @param $user
     */
    public function toggleFollow($user)
    {
        $this->isFollowing($user) ? $this->unfollow($user) : $this->follow($user);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            UserFollow::class,
            'following_id',
            'follower_id'
        )->withTimestamps();
    }

    public function followings(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            UserFollow::class,
            'follower_id',
            'following_id'
        )->withTimestamps();
    }

    public function attachFollowStatus($data, callable $resolver = null)
    {
        $returnFirst = false;

        switch (true) {
            case $data instanceof Model:
                $returnFirst = true;
                $data = collect([$data]);

                break;

            case $data instanceof LengthAwarePaginator:
                $data = $data->getCollection();

                break;

            case $data instanceof Paginator:
                $data = collect($data->items());

                break;

            case is_array($data):
                $data = collect($data);

                break;
        }

        abort_if(!$data instanceof Collection, 422, 'Invalid $data type.');

        // 临时解决一下
        $getKey = 'user_id';
        if ($data->first() instanceof User) {
            $getKey = 'id';
        } elseif ($data->first() instanceof UserVisit) {
            $getKey = 'visitor_id';
        }

        $userIds = $data->pluck($getKey);

        $followed = $this->followings()->whereIn('following_id', $userIds)->pluck('following_id');

        $data->map(function (Model $followable) use ($followed, $resolver) {
            $resolver ??= fn ($m) => $m;
            $followable = $resolver($followable);

            if (in_array(FollowTraits::class, class_uses($followable))) {
                $followable->setAttribute('has_followed', $followed->contains($followable->getKey()));
            }
        });

        return $returnFirst ? $data->first() : $data;
    }
}
