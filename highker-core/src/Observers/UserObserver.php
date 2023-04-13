<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Observers;

use HighKer\Core\Models\User;

class UserObserver
{
    public function updating(User $user)
    {
        if ($user->name !== $user->getOriginal('name')) {
            if ($user->name == '昵称已重置') {
                $user->name_edited_at = null;
            } else {
                $user->name_edited_at = $user->freshTimestamp();
            }
        }
    }
}
