<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Observers;

use HighKer\Core\Enum\UserTask;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Subscription;
use HighKer\Core\Models\TaskLog;
use HighKer\Core\Models\Topic;

class SubscriptionObserver
{
    /**
     * @throws HighKerException
     */
    public function created(Subscription $subscription)
    {
        if ($subscription->subscribable instanceof Topic) {
            $subscription->subscribable->increment('follow_count');

            // 新手任务 关注话题
            TaskLog::onceTask(UserTask::FOLLOW_TOPIC);
        }
    }

    public function deleted(Subscription $subscription)
    {
        if ($subscription->subscribable instanceof Topic) {
            $subscription->subscribable->decrement('follow_count');
        }
    }
}
