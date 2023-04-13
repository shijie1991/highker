<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support\Wechat\Handler;

use HighKer\Core\Enum\FeedStatus;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Administrator;
use HighKer\Core\Models\Feed;
use HighKer\Core\Notifications\FeedForbiddenNotifications;
use HighKer\Core\Support\HighKer;
use Illuminate\Support\Facades\Redis;

/**
 * 微信小程序 音视频内容安全识别 回调.
 */
class MediaCheckHandler
{
    /**
     * @throws HighKerException
     */
    public function __invoke($message, \Closure $next)
    {
        // 获取审核任务类型
        [$key] = Highker::getCacheKey('audit:type', 'task-type', [$message['trace_id']]);
        $auditType = Redis::get($key);
        Redis::del($key);

        // 如果是动态审核
        if ($auditType == 'feed') {
            // 获取与任务 ID 关联的动态 ID
            [$key] = Highker::getCacheKey('audit:feed', 'task-id', [$message['trace_id']]);
            $feedId = Redis::get($key);
            Redis::del($key);

            // 设置 审核结果哈希集合
            [$resultKey] = Highker::getCacheKey('audit:feed', 'audit-result', [$feedId]);
            if ($message['result']['suggest'] == 'pass') {
                Redis::hset($resultKey, $message['trace_id'], true);
            }

            // 获取与动态关联的 任务列表
            [$key] = Highker::getCacheKey('audit:feed', 'task-list', [$feedId]);
            // 获取集合的成员数量
            $count = Redis::scard($key);
            // 如果只剩下一个成员 则设置 动态整体状态
            if ($count == 1) {
                // 获取哈希表中所有值。
                $values = Redis::hvals($resultKey);
                Redis::del($resultKey);

                // 设置 动态状态
                $status = collect($values)->flip()->has(0) ? FeedStatus::FORBIDDEN : FeedStatus::APPROVE;
                $feed = Feed::query()->withoutGlobalScope('approve')->find($feedId);
                $feed->status = $status;
                $feed->save();

                // 如果审核状态为 屏蔽 发送通知
                if ($status == FeedStatus::FORBIDDEN) {
                    // 发送 系统通知
                    $feed->user->notify(new FeedForbiddenNotifications(Administrator::query()->find(1), '您发布的动态因违反社区规定审核未通过，已被屏蔽。'));
                }
            }
            // 移除集合中的成员
            Redis::srem($key, $message['trace_id']);
        }

        return 'success';
    }
}
