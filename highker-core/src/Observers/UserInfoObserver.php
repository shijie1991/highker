<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Observers;

use HighKer\Core\Models\UserInfo;
use HighKer\Core\Utils\StringUtils;

class UserInfoObserver
{
    public function updating(UserInfo $userInfo)
    {
        // 如果生日变化
        if ($userInfo->birthday !== $userInfo->getOriginal('birthday')) {
            $birthday = now()->parse($userInfo->birthday);
            // 设置 星座
            $userInfo->signs = StringUtils::getSigns($birthday->format('m'), $birthday->format('d'));
        }
    }
}
