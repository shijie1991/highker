<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Utils;

/**
 * Class PhoneUtils.
 */
class PhoneUtils
{
    /**
     * 判断是否是合法的手机号.
     *
     * @param $phoneNo
     *
     * @return bool
     */
    public static function isPhone($phoneNo)
    {
        $phoneNo = strval($phoneNo);

        if (preg_match('/^1[3456789]{1}\\d{9}$/', $phoneNo)) {
            return true;
        }

        return false;
    }
}
