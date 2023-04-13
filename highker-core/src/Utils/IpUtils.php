<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Utils;

use Zhuzhichao\IpLocationZh\Ip;

class IpUtils
{
    /**
     * 获取当前客户端ip.
     *
     * @return array|false|string
     */
    public static function getIp()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_REAL_IP')) {
            $ip = getenv('HTTP_X_REAL_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            $ips = explode(',', $ip);
            $ip = $ips[0];
        } elseif (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = '0.0.0.0';
        }

        return $ip;
    }

    public static function getLocationNames($ip = '')
    {
        if (!$ip) {
            $ip = self::getIp();
        }

        $location = Ip::find($ip);

        if (is_array($location)) {
            if ($location[2] == '本机地址') {
                return null;
            }

            return $location[2] ?? null;
        }

        return null;
    }
}
