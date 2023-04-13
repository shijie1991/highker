<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\AccountRegisterType;
use HighKer\Core\Enum\ClientType;
use HighKer\Core\Utils\PhoneUtils;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 登录错误日志.
 *
 * @property int    $id
 * @property int    $account_id 本次登录的账号id，可能为null
 * @property string $login_time 本次登录的时间
 * @property string $account    本次登录的账号
 * @property string $password   账号密码
 * @property string $ip         本次登录的账号的ip
 * @property string $login_type 本次登录的方式 pc ios android等
 * @property string $login_site 本次登录的站点
 * @property string $open_type  本次登录的通行证方式 如 weixin qq phone email
 */
class LoginErrorLog extends BaseModel
{
    public static function boot()
    {
        parent::boot();
    }

    public static function addFromPc($account, $password, $ip)
    {
        $log = new LoginErrorLog();
        $log->account = $account;
        $log->password = $password;
        $log->ip = $ip;
        if (PhoneUtils::isPhone($account)) {
            $log->open_type = AccountRegisterType::PHONE;
        }
        $log->login_type = ClientType::PC;

        $success = $log->save();
        if (!$success) {
            return false;
        }

        return $log;
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
