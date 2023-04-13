<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Utils\PhoneUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Throwable;

/**
 * 绑定手机.
 *
 * @property int    id
 * @property string phone
 * @property int    account_id
 * @property bool   is_active
 * @property int    login_count
 * @property string logined_at
 * @property string created_at
 * @property string updated_at
 */
class AccountPhone extends BaseModel
{
    protected $attributes = ['login_count' => 0];

    protected $casts = ['is_active' => 'boolean'];

    protected $fillable = ['logined_at', 'login_count'];

    /**
     * 根据账号获取当前激活的手机.
     *
     * @param $accountId
     *
     * @return null|Builder|Model|object
     */
    public static function getActive($accountId)
    {
        return AccountPhone::onWriteConnection()->where('account_id', $accountId)->where('is_active', true)->first();
    }

    /**
     * 根据手机号获取当前激活的账号.
     *
     * @param $phone
     *
     * @return null|Builder|Model|object
     */
    public static function getActiveByPhone($phone)
    {
        return AccountPhone::onWriteConnection()->where('phone', $phone)->where('is_active', true)->first();
    }

    /**
     * 获取 手机账号信息.
     *
     * @param $accountId
     * @param $phone
     *
     * @return null|Builder|Model|object
     */
    public static function getPhone($accountId, $phone)
    {
        return AccountPhone::onWriteConnection()->where('phone', $phone)->where('account_id', $accountId)->first();
    }

    /**
     * 绑定一个新的手机号.
     *
     * @throws Throwable
     * @throws HighKerException
     *
     * @return bool
     */
    public static function bind(int $accountId, string $phone)
    {
        if (!PhoneUtils::isPhone($phone)) {
            throw new HighKerException('手机号格式不正确');
        }

        // 判断手机是否有绑定
        $newPhone = self::getActiveByPhone($phone);
        if ($newPhone) {
            if ($newPhone->account_id != $accountId) {
                throw new HighKerException('手机号已经被绑定');
            }

            return true;
        }

        return Highker::db()->transaction(function () use ($accountId, $phone) {
            // 删除以前的绑定
            if ($oldPhone = AccountPhone::getActive($accountId)) {
                if ($oldPhone->phone == $phone) {
                    return true;
                }
                $oldPhone->is_active = false;
                $oldPhone->save();
            }

            // 以前绑定过，更新绑定
            if ($havePhone = AccountPhone::getPhone($accountId, $phone)) {
                $havePhone->is_active = true;
                $havePhone->save();

                return true;
            }

            // 绑定 新手机
            $newPhone = new AccountPhone();
            $newPhone->account_id = $accountId;
            $newPhone->is_active = true;
            $newPhone->phone = $phone;
            $newPhone->save();

            return true;
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
