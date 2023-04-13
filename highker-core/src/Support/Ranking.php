<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support;

use HighKer\Core\Enum\ResponseCode;
use HighKer\Core\Enum\UserRanking;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Redis;

class Ranking
{
    /**
     * @throws HighKerException
     */
    public static function incrementRank(string $slug, User|Authenticatable $user, $increment = 1)
    {
        if (!in_array($slug, UserRanking::LIST)) {
            throw new HighKerException('该榜单不存在');
        }

        [$key, $expire] = Highker::getCacheKey('user:ranking', 'list', [$slug, now()->weekOfYear]);

        Redis::ZINCRBY($key, $increment, $user->id);
        Redis::expire($key, $expire);

        // 根据其他榜单 设置 用户活跃度
        if ($slug !== UserRanking::USER) {
            $increment = UserRanking::MAP[$slug]['score'];

            [$key, $expire] = Highker::getCacheKey('user:ranking', 'list', [UserRanking::USER, now()->weekOfYear]);

            Redis::ZINCRBY($key, $increment, $user->id);
            Redis::expire($key, $expire);
        }
    }

    /**
     * @throws HighKerException
     */
    public static function decrementRank(string $slug, User|Authenticatable $user)
    {
        if (!in_array($slug, UserRanking::LIST)) {
            throw new HighKerException('该榜单不存在');
        }

        [$key, $expire] = Highker::getCacheKey('user:ranking', 'list', [$slug, now()->weekOfYear]);

        Redis::ZINCRBY($key, -1, $user->id);
        Redis::expire($key, $expire);

        // 根据其他榜单 设置 用户活跃度
        if ($slug !== UserRanking::USER) {
            $increment = UserRanking::MAP[$slug]['score'];

            [$key, $expire] = Highker::getCacheKey('user:ranking', 'list', [UserRanking::USER, now()->weekOfYear]);

            Redis::ZINCRBY($key, -$increment, $user->id);
            Redis::expire($key, $expire);
        }
    }

    /**
     * @throws HighKerException
     */
    public static function getRanking(string $slug)
    {
        if (!in_array($slug, UserRanking::LIST)) {
            throw new HighKerException('该榜单不存在');
        }

        // 是今年的 第几周
        $week = now()->weekOfYear;

        // 如果是周一 择取上一周的排行榜
        // if (now()->dayOfWeek) {
        //     $week = now()->subDays()->weekOfYear;
        // }

        [$key] = HighKer::getCacheKey('user:ranking', 'list', [$slug, $week]);

        $ranking = collect(Redis::zrevrange($key, 0, 9, ['WITHSCORES' => true]));

        if ($ranking->isEmpty()) {
            throw new HighKerException('暂无榜单数据', ResponseCode::OK);
        }

        $userList = User::query()->whereIn('id', $ranking->keys())->get();

        $userList->map(function ($item) use ($ranking) {
            $item->ranking = $ranking[$item->id];
        });

        return $userList->sortBy('ranking', SORT_NUMERIC, true)->values();
    }
}
