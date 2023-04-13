<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Channels;

use Illuminate\Notifications\Notification;

/**
 * WebSocket 渠道.
 *
 * Class WebsocketChannel
 */
class WebsocketChannel
{
    /**
     * 发送指定的通知。
     */
    public function send(mixed $notifiable, Notification $notification)
    {
        $notification->toWebSocket($notifiable);
    }
}
