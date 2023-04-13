<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Observers;

use HighKer\Core\Enum\UserRanking;
use HighKer\Core\Enum\UserTask;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Comment;
use HighKer\Core\Models\Feed;
use HighKer\Core\Models\Like;
use HighKer\Core\Models\TaskLog;
use HighKer\Core\Notifications\CommentLikeNotifications;
use HighKer\Core\Notifications\FeedLikeNotifications;
use HighKer\Core\Support\Ranking;

class LikeObserver
{
    public function saving(Like $like)
    {
        $like->user_id = $like->user_id ?: auth()->id();
    }

    /**
     * @throws HighKerException
     */
    public function created(Like $like)
    {
        if ($like->likeable instanceof Feed) {
            // 记录得分
            $score = $like->likeable->created_at > now()->subDays(7) ? 4 : 2;
            $like->likeable->increment('score', $score);

            // 冗余点赞数+1
            $like->likeable->increment('like_count');

            // 动态点赞任务
            TaskLog::dailyTask(UserTask::LIKE_FEED);

            // 发送动态通知
            $like->likeable->user->notify(new FeedLikeNotifications($like->user, $like->likeable));
        }

        if ($like->likeable instanceof Comment) {
            // 冗余点赞数+1
            $like->likeable->increment('like_count');
            $like->user->notify(new CommentLikeNotifications($like->user, $like->likeable));
        }

        Ranking::incrementRank(UserRanking::LIKE, $like->user);
    }

    /**
     * @throws HighKerException
     */
    public function deleted(Like $like)
    {
        if ($like->likeable instanceof Feed) {
            // 记录得分
            $score = $like->likeable->created_at->diff($like->created_at)->days < 7 ? 4 : 2;
            $like->likeable->decrement('score', $score);
            // 冗余点赞数-1
            $like->likeable->decrement('like_count');
        }

        if ($like->likeable instanceof Comment) {
            // 冗余点赞数-1
            $like->likeable->decrement('like_count');
        }

        Ranking::decrementRank(UserRanking::LIKE, $like->user);
    }
}
