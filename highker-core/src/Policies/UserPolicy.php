<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Policies;

use HighKer\Core\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy extends Policy
{
    public function locked(User $user)
    {
        return $user->locked_at === null ? Response::allow() : Response::deny('账号被锁定');
    }

    public function follow(User $user, User $followUser)
    {
        return $user->id !== $followUser->id ? Response::allow() : Response::deny('不能关注自己');
    }

    public function sendMessage(User $user, User $followUser)
    {
        return $user->id !== $followUser->id ? Response::allow() : Response::deny('不能和自己对话');
    }
}
