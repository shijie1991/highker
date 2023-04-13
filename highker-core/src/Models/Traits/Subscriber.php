<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models\Traits;

use Exception;
use HighKer\Core\Models\Subscription;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Trait Subscriber.
 */
trait Subscriber
{
    public function subscribe(Model $object)
    {
        if (!$this->hasSubscribed($object)) {
            $subscribe = app(subscription::class);
            $subscribe->user_id = $this->getKey();
            $subscribe->subscribable_id = $object->getKey();
            $subscribe->subscribable_type = $object->getMorphClass();

            $this->subscriptions()->save($subscribe);
        }
    }

    public function unsubscribe(Model $object)
    {
        $relation = $this->subscriptions()
            ->where('subscribable_id', $object->getKey())
            ->where('subscribable_type', $object->getMorphClass())
            ->where('user_id', $this->getKey())
            ->first()
        ;

        $relation?->delete();
    }

    /**
     * @throws Exception
     */
    public function toggleSubscribe(Model $object)
    {
        $this->hasSubscribed($object) ? $this->unsubscribe($object) : $this->subscribe($object);
    }

    public function hasSubscribed(Model $object): bool
    {
        return tap($this->relationLoaded('subscriptions') ? $this->subscriptions : $this->subscriptions())
            ->where('subscribable_id', $object->getKey())
            ->where('subscribable_type', $object->getMorphClass())
            ->count() > 0;
    }

    public function attachSubscriptionStatus($subscribables, callable $resolver = null)
    {
        $returnFirst = false;

        switch (true) {
            case $subscribables instanceof Model:
                $returnFirst = true;
                $subscribables = collect([$subscribables]);

                break;

            case $subscribables instanceof LengthAwarePaginator:
                $subscribables = $subscribables->getCollection();

                break;

            case $subscribables instanceof Paginator:
                $subscribables = collect($subscribables->items());

                break;

            case is_array($subscribables):
                $subscribables = collect($subscribables);

                break;
        }

        abort_if(!($subscribables instanceof Collection), 422, 'Invalid $subscribables type.');

        $subscribed = $this->subscriptions()->get();

        $subscribables->map(
            function ($subscribable) use ($subscribed, $resolver) {
                $resolver ??= fn ($m) => $m;
                $subscribable = $resolver($subscribable);

                if ((bool) $subscribable && in_array(Subscribable::class, class_uses($subscribable))) {
                    $subscribable->setAttribute(
                        'has_subscribed',
                        $subscribed->where('subscribable_id', $subscribable->getKey())
                            ->where('subscribable_type', $subscribable->getMorphClass())
                            ->count() > 0
                    );
                }
            }
        );

        return $returnFirst ? $subscribables->first() : $subscribables;
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id', $this->getKeyName());
    }
}
