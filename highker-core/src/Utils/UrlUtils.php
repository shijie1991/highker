<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Utils;

class UrlUtils
{
    /**
     * 获取url中的host.
     *
     * @param $url
     *
     * @return false|mixed
     */
    public static function getHost($url)
    {
        $pieces = parse_url($url);
        if (!$pieces) {
            return false;
        }

        return $pieces['host'];
    }
}
