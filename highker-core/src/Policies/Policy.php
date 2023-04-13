<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class Policy
{
    use HandlesAuthorization;

    // public function before($user, $ability)
    // {
    //     // 如果用户拥有管理内容的权限的话，即授权通过
    //     if ($user->can('manage_contents')) {
    //         return true;
    //     }
    // }
}
