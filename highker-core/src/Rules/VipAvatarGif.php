<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class VipAvatarGif.
 */
class VipAvatarGif implements Rule
{
    public function passes($attribute, $value)
    {
        if (Str::endsWith($value->getMimeType(), 'gif')) {
            return Auth::user()->is_vip == true;
        }

        return true;
    }

    public function message()
    {
        return 'VIP 会员才能上传动态头像';
    }
}
