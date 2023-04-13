<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Observers;

use HighKer\Core\Models\ChatMessage;
use HighKer\Core\Notifications\MessageNotifications;
use Illuminate\Support\Facades\Auth;

class ChatMessageObserver
{
    public function created(ChatMessage $message)
    {
        // 判断 该给谁发送消息
        $notificationUser = Auth::id() === $message->conversation->sender ? 'receiver_user' : 'sender_user';

        if ($message->conversation->private) {
            $message->loadMissing(['sender_user']);
        } else {
            $message->loadMissing(['secret_user']);
            $message->secret_user->makeHidden(['id', 'name', 'avatar', 'status', 'gender', 'level', 'is_vip']);
            $message->secret_user->makevisible(['fake_avatar', 'fake_name']);
        }

        // 给接收方发送 websocket 消息
        $message->conversation->{$notificationUser}->notifyNow(new MessageNotifications($message));
    }
}
