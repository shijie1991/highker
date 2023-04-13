<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\AccountRegisterType;
use HighKer\Core\Exceptions\HighKerException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class AccountBase
 * 开放平台通行证的基类.
 *
 * @property int    $id
 * @property string $open_id      开放平台的open_id
 * @property int    $account_id   账号ID
 * @property string $create_time  创建时间
 * @property string $access_token 授权登录的令牌
 * @property string logined_at    使用此OPENID登录的最后登录时间
 * @property int    login_count   使用此OPENID登录的次数
 * @property string $expire_time 令牌的过期时间
 * @property string gender 性别
 * @property string $face 头像
 */
abstract class AccountBase extends BaseModel
{
    public const TYPE = 'unknown';

    abstract public function setParams($params);

    /**
     * @param $openId
     * @param $openType
     *
     * @return null|Builder|Model|object
     */
    public static function getByOpenId($openId, $openType)
    {
        /** @var Model $model */
        $model = self::getModel($openType);

        $field = 'open_id';

        return $model::onWriteConnection()->where($field, $openId)->first();
    }

    /**
     * @param $accountId
     * @param $openType
     *
     * @return null|Builder|Model|object
     */
    public static function getByAccountId($accountId, $openType)
    {
        /** @var BaseModel $model */
        $model = self::getModel($openType);

        return $model::onWriteConnection()->where('account_id', $accountId)->first();
    }

    /**
     * @param $openType
     */
    public static function getModel($openType): ?string
    {
        return match ($openType) {
            AccountRegisterType::MINI_PROGRAM, AccountRegisterType::WECHAT => AccountWechat::class,
            AccountRegisterType::FAKER => AccountFaker::class,
            default                    => null,
        };
    }

    /**
     * @param $accountId
     * @param $openId
     * @param $openType
     * @param $params
     *
     * @throws HighKerException
     */
    public static function createOpen($accountId, $openId, $openType, $params): AccountBase
    {
        $model = self::getModel($openType);

        /** @var Model $model */
        if ($model::query()->where('open_id', $openId)->exists()) {
            throw new HighKerException('开放平台账号已绑定!');
        }

        /** @var AccountBase $open */
        $open = new $model();
        $open->setParams($params);
        $open->open_id = $openId;
        $open->account_id = $accountId;
        $open->logined_at = now()->toDateTimeString();
        $open->login_count = 1;

        $success = $open->save();
        if (!$success) {
            throw new HighKerException('创建开放平台账号失败!');
        }

        return $open;
    }

    /**
     * @param $accessToken
     * @param $params
     */
    public function updateOpen($accessToken, $params)
    {
        $this->access_token = $accessToken;
        ++$this->login_count;
        $this->logined_at = now()->toDateTimeString();
        $this->setParams($params);
        $this->save();
    }

    public function getType()
    {
        return self::TYPE;
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
