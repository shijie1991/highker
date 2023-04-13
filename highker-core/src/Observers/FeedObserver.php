<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Observers;

use HighKer\Core\Enum\FeedStatus;
use HighKer\Core\Enum\UserRanking;
use HighKer\Core\Enum\UserTask;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Feed;
use HighKer\Core\Models\TaskLog;
use HighKer\Core\Models\User;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Support\Ranking;
use HighKer\Core\Utils\IpUtils;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class FeedObserver
{
    /**
     * @throws HighKerException
     */
    public function creating(Feed $feed)
    {
        if (!app()->runningInConsole()) {
            static::throttleCheck($feed->user);
        }

        $feed->location = IpUtils::getLocationNames();
    }

    /**
     * @throws HighKerException
     */
    public function created(Feed $feed)
    {
        // 创建动态任务
        TaskLog::dailyTask(UserTask::CREATE_FEED);

        // 冗余动态数+1
        $feed->user->info()->increment('feed_count');

        Ranking::incrementRank(UserRanking::ADD_FEED, $feed->user);
    }

    public function updated(Feed $feed)
    {
        // 如果修改了 status 字段 更新关联话题
        if (collect($feed->getChanges())->has('status')) {
            if ($feed->status == FeedStatus::APPROVE) {
                $feed->topics()->increment('feed_count');
            } else {
                $feed->topics()->decrement('feed_count');
            }
        }
    }

    /**
     * @throws HighKerException
     */
    public function deleted(Feed $feed)
    {
        // 冗余动态数-1
        $feed->user->info()->decrement('feed_count');

        Ranking::decrementRank(UserRanking::ADD_FEED, $feed->user);
    }

    /**
     * @throws HighKerException
     */
    public static function throttleCheck(User $user)
    {
        $lastFeed = $user->feeds()->latest()->first();
        if ($lastFeed) {
            // 可以发布的时间 = (上一条发布时间 + 节流时间)
            $canCreatedTime = $lastFeed->created_at->addSeconds(config('core.throttle.feed_create'));
            // 当前时间 小于 可以发布的时间
            if (now() < $canCreatedTime) {
                throw new HighKerException('操作过于频繁，请稍后再试');
            }
        }
    }
}
