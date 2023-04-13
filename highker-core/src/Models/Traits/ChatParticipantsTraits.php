<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models\Traits;

use HighKer\Core\Models\ChatConversation;
use HighKer\Core\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

trait ChatParticipantsTraits
{
    protected User $sender;
    protected ChatConversation $conversations;

    /**
     * 设置消息的发送者.
     *
     * @return $this
     */
    public function from(Model|User|Authenticatable $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * 设置 针对的 对话.
     *
     * @return $this
     */
    public function to(ChatConversation $conversations): self
    {
        $this->conversations = $conversations;

        return $this;
    }
}
