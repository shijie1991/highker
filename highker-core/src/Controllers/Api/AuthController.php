<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use EasyWeChat\Kernel\Exceptions\BadResponseException;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Account;
use HighKer\Core\Requests\MiniRegisterRequest;
use HighKer\Core\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        if (Auth::check()) {
            return $this->fail('请勿重复登录');
        }

        $request->validate(
            ['phone' => 'required', 'password' => 'required'],
            ['phone.required' => '请输入手机号', 'password.required' => '请输入密码']
        );

        try {
            $result = Account::loginByPhone($request->input('phone'), $request->input('password'));

            return $this->success($result);
        } catch (HighKerException $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     *
     * @return JsonResource|JsonResponse|void
     */
    public function miniLogin(Request $request)
    {
        $request->validate(['code' => 'required'], ['code.required' => '参数错误']);

        if (Auth::check()) {
            return $this->fail('请勿重复登录');
        }

        try {
            $result = Account::miniLogin($request->input('code'));

            return $this->success($result);
        } catch (HighKerException $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @throws HighKerException
     * @throws Throwable
     *
     * @return JsonResource|JsonResponse|void
     */
    public function miniRegister(MiniRegisterRequest $request)
    {
        if (Auth::check()) {
            return $this->fail('请勿重复登录');
        }

        $result = Account::miniRegister($request);

        return $this->success($result);
    }

    /**
     * 获取当前登陆用户信息.
     */
    public function me()
    {
        $user = Auth::user()->loadMissing('info')->makeVisible('vip_expired_at');

        return $this->success(UserResource::make($user));
    }

    /**
     * 退出登陆.
     *
     * @return JsonResource|JsonResponse
     */
    public function logout()
    {
        Auth::user()->tokens()->delete();

        return $this->success(null, '退出登录成功');
    }

    /**
     * 账户注销
     */
    public function destroy()
    {
        Account::deleteAccount(Auth::user()->account_id);

        Auth::user()->tokens()->delete();

        return $this->success(null, '账号注销成功');
    }
}
