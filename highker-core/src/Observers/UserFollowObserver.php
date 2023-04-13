<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Observers;

use HighKer\Core\Enum\UserTask;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\TaskLog;
use HighKer\Core\Models\UserFollow;
use HighKer\Core\Notifications\FollowNotifications;

class UserFollowObserver
{
    /**
     * @throws HighKerException
     */
    public function created(UserFollow $userFollow)
    {
        $userFollow->following_user->info->increment('fans_count');
        $userFollow->follower_user->info->increment('follow_count');

        // 新手任务 关注用户
        TaskLog::onceTask(UserTask::FOLLOW_USER);

        // 发送动态通知
        $userFollow->following_user->notify(new FollowNotifications($userFollow->follower_user, $userFollow->following_user));
    }

    public function deleted(UserFollow $userFollow)
    {
        $userFollow->following_user->info->decrement('fans_count');
        $userFollow->follower_user->info->decrement('follow_count');
    }
}
