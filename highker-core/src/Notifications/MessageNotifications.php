<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Notifications;

use HighKer\Core\Enum\WebSocketNoticeType;
use HighKer\Core\Models\ChatMessage;

class MessageNotifications extends BaseSaeNotifications
{
    private ChatMessage $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    public function toWebSocket($notifiable)
    {
        $data = [
            'type'    => WebSocketNoticeType::CHAT,
            'message' => $this->message,
        ];

        // 发送 Websocket 消息
        app('saeChannel')->sendMessage($notifiable->id, json_encode($data));
    }
}
