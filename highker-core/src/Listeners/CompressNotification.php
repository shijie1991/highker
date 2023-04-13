<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Listeners;

use HighKer\Core\Channels\DatabaseChannel;
use HighKer\Core\Enum\NoticeEvent;
use HighKer\Core\Enum\NoticeType;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Arr;

class CompressNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event)
    {
        // 如果不是 站内信 则不处理
        if ($event->channel !== DatabaseChannel::class) {
            return;
        }

        $currentNotification = $event->response;

        // 如果当前站内信 不属于 互动类型的 不处理
        if ($currentNotification->notice_type !== NoticeType::INTERACTIVE) {
            return;
        }

        // 如果不属于 互动类型的 不处理
        if (!in_array($currentNotification->event, NoticeEvent::INTERACTIVE)) {
            return;
        }

        // 查找相同 target 的一定时间段内所有未读消息
        $unreadNotifications = $event->notifiable->unreadNotifications()
            ->where('id', '<>', $currentNotification->id)
            ->where('data->trigger->type', $currentNotification->data['trigger']['type'])
            ->where('data->target->id', $currentNotification->data['target']['id'])
            ->whereDate('created_at', '>=', $currentNotification->created_at->subDay(3)->toDateString())
            ->get()
        ;

        if ($unreadNotifications->isNotEmpty()) {
            $users = [];

            // 获取 Data 字段
            $dataList = $unreadNotifications->pluck('data');
            // 获取 没有消息压缩的 数量
            $singleCount = $dataList->whereNull('compress_count')->count();
            // 获取 压缩了的消息并计算压缩数量
            $compressDataCount = $dataList->whereNotNull('compress_count')->pluck('compress_count')->sum();

            foreach ($unreadNotifications as $notification) {
                $users = $notification->data['trigger']['users'];

                // 最多存储三个触发者
                if (count($users) < 3) {
                    // 只记录不同的用户
                    $ids = Arr::pluck($users, 'id');
                    if (!in_array($currentNotification->data['trigger']['users'][0]['id'], $ids)) {
                        $users = array_merge($users, $currentNotification->data['trigger']['users']);
                    }
                }
                // 删除该条已合并的记录
                $notification->delete();
            }

            $data = $currentNotification->data;
            $data['compress_count'] = $compressDataCount + $singleCount + 1;
            $data['trigger']['users'] = $users;

            $currentNotification->data = $data;
            $currentNotification->save();
        }
    }
}
