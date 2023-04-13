<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Policies;

use HighKer\Core\Models\ChatConversation;
use HighKer\Core\Models\User;
use Illuminate\Auth\Access\Response;

class ChatConversationPolicy extends Policy
{
    public function delete(User $user, ChatConversation $conversation): Response
    {
        if ($conversation->exists) {
            $between = [$conversation->sender_user->id, $conversation->receiver_user->id];

            return in_array($user->id, $between) ? Response::allow() : Response::deny('没有权限删除该对话');
        }

        return Response::deny('没有权限删除该对话');
    }
}
