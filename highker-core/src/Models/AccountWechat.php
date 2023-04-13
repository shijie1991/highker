<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\AccountRegisterType;
use HighKer\Core\Enum\UserGender;

/**
 * @property string $refresh_token
 * @property string $scope
 * @property string $union_id
 */
class AccountWechat extends AccountBase
{
    public const TYPE = AccountRegisterType::WECHAT;

    /**
     * @param $params
     */
    public function setParams($params)
    {
        $this->union_id = $params['unionid'];

        $gender = [
            '2' => UserGender::FEMALE,
            '1' => UserGender::MALE,
            '0' => UserGender::UNKNOWN,
        ];

        if (isset($params['gender'])) {
            $this->gender = array_key_exists($params['gender'], $gender) ? $gender[$params['gender']] : UserGender::UNKNOWN;
        }
    }
}
