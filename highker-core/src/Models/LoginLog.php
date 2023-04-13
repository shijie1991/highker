<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Utils\IpUtils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LoginLog.
 *
 * @property int id
 * @property int account_id
 * @property int open_id
 * @property int open_type
 * @property int login_type
 * @property int ip
 * @property int created_at
 * @property int updated_at
 */
class LoginLog extends BaseModel
{
    protected $fillable = ['account_id', 'open_id', 'open_type', 'login_type', 'ip'];

    public static function boot()
    {
        parent::boot();
    }

    /**
     * @param $accountId
     * @param $openId
     * @param $openType
     * @param $loginType
     *
     * @return Builder|Model
     */
    public static function createLog($accountId, $openId, $openType, $loginType)
    {
        return LoginLog::query()->create([
            'account_id' => $accountId,
            'open_id'    => $openId,
            'open_type'  => $openType,
            'login_type' => $loginType,
            'ip'         => IpUtils::getIp(),
        ]);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
