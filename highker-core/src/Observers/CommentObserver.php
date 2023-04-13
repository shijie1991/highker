<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Observers;

use HighKer\Core\Enum\CommentLevel;
use HighKer\Core\Enum\UserRanking;
use HighKer\Core\Enum\UserTask;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Comment;
use HighKer\Core\Models\TaskLog;
use HighKer\Core\Models\User;
use HighKer\Core\Notifications\CommentNotifications;
use HighKer\Core\Notifications\CommentReplyNotifications;
use HighKer\Core\Support\Ranking;

class CommentObserver
{
    public function creating(Comment $comment)
    {
        $comment->level = CommentLevel::COMMENT;
        // 如果 parent_id 则是一级回复
        if ($comment->parent_id) {
            $comment->level = CommentLevel::REPLY;
        }
        // 如果 reply_id 则是二级回复
        if ($comment->reply_id) {
            $comment->level = CommentLevel::SECOND_REPLY;
        }

        if (!app()->runningInConsole()) {
            static::throttleCheck($comment->user);
        }
    }

    /**
     * @throws HighKerException
     */
    public function created(Comment $comment)
    {
        // 冗余用户评论数+1
        $comment->user->info()->increment('comment_count');

        $score = 0;
        if ($comment->level == CommentLevel::COMMENT) {
            $score = $comment->created_at > now()->subDays(7) ? 8 : 4;
            // 评论消息通知
            $comment->feed->user->notify(new CommentNotifications($comment->user, $comment->feed, $comment));

            // 评论任务
            TaskLog::dailyTask(UserTask::COMMENT);
        } else {
            if ($comment->level === CommentLevel::REPLY) {
                $score = $comment->created_at > now()->subDays(7) ? 4 : 2;
                // 回复一级评论通知
                $comment->parent->user->notify(new CommentReplyNotifications($comment->user, $comment->parent, $comment));
            }
            // 如果不是评论而是回复的话 冗余回复数+1
            $comment->parent()->increment('reply_count');

            // 如果是二级回复 冗余回复数 +1
            if ($comment->level === CommentLevel::SECOND_REPLY) {
                $score = $comment->created_at > now()->subDays(7) ? 2 : 1;

                $comment->reply_parent()->increment('reply_count');

                // 回复二级评论通知
                $comment->reply_parent->user->notify(
                    new CommentReplyNotifications($comment->user, $comment->reply_parent, $comment)
                );
            }
        }

        // 记录得分
        $comment->feed->increment('score', $score);
        // 冗余点赞数+1
        $comment->feed->increment('comment_count');

        Ranking::incrementRank(UserRanking::COMMENT, $comment->user);
    }

    /**
     * @throws HighKerException
     */
    public function deleted(Comment $comment)
    {
        // 冗余评论数-1
        $comment->user->info()->decrement('comment_count');

        Ranking::decrementRank(UserRanking::COMMENT, $comment->user);
    }

    // 评论回复 节流
    public static function throttleCheck(User $user)
    {
        $lastComment = $user->comments()->latest()->first();
        if ($lastComment) {
            // 可以发布的时间 = (上一条发布时间 + 节流时间)
            $canCreatedTime = $lastComment->created_at->addSeconds(config('core.throttle.comment_create'));
            // 当前时间 小于 可以发布的时间
            if (now() < $canCreatedTime) {
                abort(429, '操作过于频繁，请稍后再试');
            }
        }
    }
}
