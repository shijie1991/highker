<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Policies;

use HighKer\Core\Models\Feed;
use HighKer\Core\Models\User;
use Illuminate\Auth\Access\Response;

class FeedPolicy extends Policy
{
    public function delete(User $user, Feed $feed): Response
    {
        return $feed->user_id == $user->id ? Response::allow() : Response::deny('没有权限删除该动态');
    }

    public function view(?User $user, Feed $feed): Response
    {
        return is_null($feed->deleted_at) ? Response::allow() : Response::deny('该动态已删除');
    }
}
