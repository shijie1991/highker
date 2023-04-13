<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models\Traits;

use HighKer\Core\Models\Like;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;

trait Liker
{
    public function like(Model $object)
    {
        $attributes = [
            'user_id'       => $this->getKey(),
            'likeable_id'   => $object->getKey(),
            'likeable_type' => $object->getMorphClass(),
        ];

        return Like::query()->where($attributes)->firstOr(
            function () use ($attributes) {
                return Like::unguarded(function () use ($attributes) {
                    if ($this->relationLoaded('likes')) {
                        $this->unsetRelation('likes');
                    }

                    return Like::query()->create($attributes);
                });
            }
        );
    }

    /**
     * @return null|bool|mixed
     */
    public function unlike(Model $object)
    {
        $relation = Like::query()
            ->where('user_id', $this->getKey())
            ->where('likeable_id', $object->getKey())
            ->where('likeable_type', $object->getMorphClass())
            ->first()
        ;

        if ($relation) {
            if ($this->relationLoaded('likes')) {
                $this->unsetRelation('likes');
            }

            return $relation->delete();
        }

        return true;
    }

    /**
     * @return null|bool|mixed
     */
    public function toggleLike(Model $object)
    {
        return $this->hasLiked($object) ? $this->unlike($object) : $this->like($object);
    }

    /**
     * @return bool
     */
    public function hasLiked(Model $object)
    {
        return ($this->relationLoaded('likes') ? $this->likes : $this->likes())
            ->where('likeable_id', $object->getKey())
            ->where('likeable_type', $object->getMorphClass())
            ->count() > 0;
    }

    public function getLikedItems(string $model)
    {
        return app($model)->whereHas(
            'likers',
            function ($q) {
                return $q->where('user_id', $this->getKey());
            }
        );
    }

    public function attachLikeStatus($likeables, callable $resolver = null)
    {
        $returnFirst = false;
        $toArray = false;

        switch (true) {
            case $likeables instanceof Model:
                $returnFirst = true;
                $likeables = collect([$likeables]);

                break;

            case $likeables instanceof LengthAwarePaginator:
                $likeables = $likeables->getCollection();

                break;

            case $likeables instanceof Paginator:
            case $likeables instanceof CursorPaginator:
                $likeables = collect($likeables->items());

                break;

            case $likeables instanceof LazyCollection:
                $likeables = collect($likeables->all());

                break;

            case is_array($likeables):
                $likeables = collect($likeables);
                $toArray = true;

                break;
        }

        abort_if(!$likeables instanceof Enumerable, 422, 'Invalid $likeables type');

        $liked = $this->likes()->get()->keyBy(function ($item) {
            return sprintf('%s:%s', $item->likeable_type, $item->likeable_id);
        });

        $likeables->map(function ($likeable) use ($liked, $resolver) {
            $resolver ??= fn ($m) => $m;
            $likeable = $resolver($likeable);

            if ($likeable && in_array(Likeable::class, \class_uses_recursive($likeable))) {
                $key = sprintf('%s:%s', $likeable->getMorphClass(), $likeable->getKey());
                $likeable->setAttribute('has_liked', $liked->has($key));
            }
        });

        return $returnFirst ? $likeables->first() : ($toArray ? $likeables->all() : $likeables);
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'user_id', $this->getKeyName());
    }
}
