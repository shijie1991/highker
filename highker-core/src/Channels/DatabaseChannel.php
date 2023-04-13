<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;

/**
 * 站内通知 渠道
 * Class DatabaseChannel.
 */
class DatabaseChannel
{
    /**
     * 发送指定的通知。
     *
     * @return mixed
     */
    public function send(mixed $notifiable, Notification $notification)
    {
        $data = $notification->toArray($notifiable);

        $event = $data['event'];
        $noticeType = $data['notice_type'];
        $lastAt = $data['last_at'] ?? null;

        $data = Arr::except($data, ['event', 'notice_type', 'last_at']);

        return $notifiable->routeNotificationFor('database')->create([
            'id'          => $notification->id,
            'data'        => $data,
            'event'       => $event,
            'notice_type' => $noticeType,
            'last_at'     => $lastAt,
        ]);
    }
}
