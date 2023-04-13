<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use EasyWeChat\Kernel\Exceptions\BadResponseException;
use HighKer\Core\Enum\AccountRegisterType;
use HighKer\Core\Enum\ClientType;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Jobs\DownloadAvatar;
use HighKer\Core\Jobs\GenerateFakeAvatar;
use HighKer\Core\Requests\MiniRegisterRequest;
use HighKer\Core\Support\Facades\Wechat;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Utils\IpUtils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

/**
 * Class Account.
 *
 * @property int id
 * @property int password
 * @property int register_type
 * @property int register_client_type
 * @property int login_count
 * @property int login_ip
 * @property int register_ip
 * @property int logined_at
 * @property int deleted_at
 * @property int created_at
 * @property int updated_at
 */
class Account extends BaseModel
{
    /* 软删除 */
    use SoftDeletes;

    protected $attributes = ['login_count' => 0];

    protected $fillable = ['login_ip', 'register_type', 'register_client_type', 'register_ip', 'password'];

    protected $hidden = ['password'];

    /**
     * 小程序 手机号登陆.
     *
     * @param $code
     *
     * @throws HighKerException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     *
     * @return array
     */
    public static function miniLogin($code)
    {
        $app = Wechat::miniApp();
        $response = $app->getUserPhone($code);
        $phoneInfo = data_get($response, 'phone_info');

        if (!$phoneInfo) {
            throw new HighKerException('解析手机号失败');
        }

        $phone = $phoneInfo['purePhoneNumber'];

        // 根据手机号获取已绑定的用户
        $accountPhone = AccountPhone::getActiveByPhone($phone);

        // 如果已注册
        if ($accountPhone) {
            try {
                return Account::miniLoginByPhone($phone);
            } catch (HighKerException $e) {
                throw new HighKerException($e->getMessage());
            } catch (Throwable) {
                throw new HighKerException('登陆失败');
            }
        } else {
            return ['phone' => $phone];
        }
    }

    /**
     * 小程序 注册用户.
     *
     * @throws HighKerException
     * @throws Throwable
     *
     * @return array|void
     */
    public static function miniRegister(MiniRegisterRequest $request)
    {
        $app = Wechat::miniApp();

        try {
            $openData = $app->getUtils()->codeToSession($request->input('code'));
        } catch (Throwable) {
            throw new HighKerException('解密失败');
        }

        if (AccountPhone::getActiveByPhone($request->input('phone'))) {
            throw new HighKerException('手机号已绑定');
        }

        $avatar = null;
        if ($request->hasFile('avatar')) {
            $avatar = Storage::putFile(Highker::uploadDir('avatar'), $request->file('avatar'));
        }

        $user = Highker::db()->transaction(function () use ($request, $avatar, $openData) {
            $account = Account::createAccount('12345678', IpUtils::getIp(), AccountRegisterType::MINI_PROGRAM, ClientType::WECHAT);
            $account->bindPhone($request->input('phone'));
            AccountBase::createOpen($account->id, $openData['openid'], AccountRegisterType::MINI_PROGRAM, $openData);

            return User::createUser($account->id, $request->input('name'), $request->input('gender'), $avatar);
        });

        if ($user) {
            // 队列生成 虚拟头像
            dispatch(new GenerateFakeAvatar($user));

            // 如果未上传头像 下载微信头像
            if (!$request->hasFile('avatar')) {
                dispatch(new DownloadAvatar($user, $request->input('avatar_url')));
            }

            return Account::miniLoginByPhone($request->input('phone'));
        }
    }

    /**
     * 微信小程序 手机号登陆.
     *
     * @param $phone
     *
     * @throws HighKerException
     *
     * @return array
     */
    public static function miniLoginByPhone($phone)
    {
        // 根据手机号获取已绑定的用户
        $accountPhone = AccountPhone::getActiveByPhone($phone);

        $account = Account::withTrashed()->find($accountPhone->account_id);
        if ($account && $account->deleted_at) {
            throw new HighKerException('账号已注销,无法登陆');
        }

        $user = User::query()->where('account_id', $accountPhone->account_id)->first();
        if (!$user) {
            throw new HighKerException('获取用户失败');
        }

        if (!$token = $user->createToken(AccountRegisterType::PHONE)->plainTextToken) {
            throw new HighKerException('登陆失败,令牌获取失败');
        }

        // 更新账号相关数据
        $accountPhone->account->logined_at = Carbon::now();
        $accountPhone->account->login_count = $accountPhone->account->login_count + 1;
        $accountPhone->account->login_ip = IpUtils::getIp();
        $accountPhone->account->save();

        $accountPhone->login_count = $accountPhone->login_count + 1;
        $accountPhone->logined_at = Carbon::now();
        $accountPhone->save();

        // 记录登录日志
        LoginLog::createLog($accountPhone->account->id, $accountPhone->id, AccountRegisterType::PHONE, ClientType::WECHAT);

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ];
    }

    /**
     * @param $phone
     * @param $password
     *
     * @throws HighKerException
     *
     * @return array
     */
    public static function loginByPhone($phone, $password)
    {
        if (!$accountPhone = AccountPhone::getActiveByPhone($phone)) {
            throw new HighKerException('账号或密码错误');
        }

        if (!$account = Account::query()->where('id', $accountPhone->account_id)->first()) {
            throw new HighKerException('账号或密码错误');
        }

        if (!static::checkPassword($password, $account->password)) {
            throw new HighKerException('账号或密码错误');
        }

        $user = User::query()->where('account_id', $accountPhone->account_id)->first();
        if (!$user) {
            throw new HighKerException('获取用户失败');
        }

        if (!$token = $user->createToken(AccountRegisterType::PHONE)->plainTextToken) {
            throw new HighKerException('登陆失败,令牌获取失败');
        }

        // 更新账号相关数据
        $accountPhone->account->logined_at = Carbon::now();
        ++$accountPhone->account->login_count;
        $accountPhone->account->login_ip = IpUtils::getIp();
        $accountPhone->account->save();

        ++$accountPhone->login_count;
        $accountPhone->logined_at = Carbon::now();
        $accountPhone->save();

        // 记录登录日志
        LoginLog::createLog($accountPhone->account->id, $accountPhone->id, AccountRegisterType::PHONE, ClientType::WECHAT);

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ];
    }

    /**
     * @param $password
     * @param $ip
     * @param $AccountType
     * @param $clientType
     *
     * @throws HighKerException
     *
     * @return Builder|Model
     */
    public static function createAccount($password, $ip, $AccountType, $clientType)
    {
        try {
            return Account::query()->create(
                [
                    'login_ip'             => $ip,
                    'register_type'        => $AccountType,
                    'register_client_type' => $clientType,
                    'register_ip'          => $ip,
                    'password'             => Account::hashPassword(trim($password)),
                ]
            );
        } catch (Throwable) {
            throw new HighKerException('账号创建失败');
        }
    }

    /**
     * 账户注销
     *
     * @param $accountId
     *
     * @return bool
     */
    public static function deleteAccount($accountId)
    {
        $account = Account::query()->find($accountId);
        if ($account) {
            $account->delete();

            return true;
        }

        return false;
    }

    /**
     * 绑定手机号.
     *
     * @throws Throwable
     * @throws HighKerException
     *
     * @return bool
     */
    public function bindPhone(string $phone)
    {
        return AccountPhone::bind($this->id, $phone);
    }

    public static function checkPassword($password, $hashPassword): bool
    {
        return Hash::check($password, $hashPassword);
    }

    public static function hashPassword($password): string
    {
        return Hash::make($password);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function phone(): hasOne
    {
        return $this->hasOne(AccountPhone::class)->where('is_active', true);
    }

    public function faker(): HasMany
    {
        return $this->hasMany(AccountFaker::class);
    }
}
