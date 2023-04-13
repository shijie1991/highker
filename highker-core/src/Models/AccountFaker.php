<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\AccountRegisterType;
use HighKer\Core\Enum\UserGender;

/**
 * Class AccountFaker.
 */
class AccountFaker extends AccountBase
{
    public const TYPE = AccountRegisterType::FAKER;

    /**
     * @param $params
     */
    public function setParams($params)
    {
        $gender = [
            '女'  => UserGender::FEMALE,
            '男'  => UserGender::MALE,
            '未知' => UserGender::UNKNOWN,
        ];

        $this->gender = array_key_exists($params['gender'], $gender) ? $gender[$params['gender']] : UserGender::UNKNOWN;
    }
}
