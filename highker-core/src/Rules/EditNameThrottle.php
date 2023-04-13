<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * Class EditNameThrottle.
 */
class EditNameThrottle implements Rule
{
    public function passes($attribute, $value)
    {
        if ($value !== Auth::user()->name) {
            if (Auth::user()->name_edited_at) {
                return now()->parse(Auth::user()->name_edited_at)->addDays(7)->lte(now());
            }

            return true;
        }

        return true;
    }

    public function message()
    {
        return '昵称每 7 天只能修改一次';
    }
}
