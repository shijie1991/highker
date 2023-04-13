<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Resources;

use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Support\HighKer;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Redis;

class UserVisitsCollectionResource extends ResourceCollection
{
    /**
     * @param $request
     *
     * @throws HighKerException
     *
     * @return array
     */
    public function toArray($request)
    {
        $user = $request->user();

        [$key] = Highker::getCacheKey('user:visit', 'count', [now()->toDateString(), $user->id]);

        return [
            'is_vip'          => $user->is_vip,
            'data'            => $this->collection,
            'visit_count'     => $user->info->visit_count,
            'day_visit_count' => Redis::get($key) ?? 0,
        ];
    }
}
