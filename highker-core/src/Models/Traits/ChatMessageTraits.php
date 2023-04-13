<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models\Traits;

use HighKer\Core\Models\ChatMessageNotification;

trait ChatMessageTraits
{
    /**
     * 离开当前对话 (删除 消息通知).
     *
     * @param $conversationId
     */
    public function leaveConversation($conversationId)
    {
        ChatMessageNotification::query()
            ->where('conversation_id', $conversationId)
            ->where('receiver', $this->getKey())
            ->delete()
        ;
    }
}
