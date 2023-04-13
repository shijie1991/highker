<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use HighKer\Core\Enum\AccountRegisterType;
use HighKer\Core\Enum\VipOrderType;
use HighKer\Core\Enum\VipProduct;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\AccountBase;
use HighKer\Core\Models\User;
use HighKer\Core\Models\VipOrder;
use HighKer\Core\Requests\VipRequest;
use HighKer\Core\Resources\CommonResource;
use HighKer\Core\Support\HighKer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;
use Yansongda\LaravelPay\Facades\Pay;

class VipController extends BaseController
{
    public function vip()
    {
        $data = [
            'user' => User::query()->find(Auth::id())->makeVisible('vip_expired_at'),
            'vip'  => VipProduct::MAP,
        ];

        return $this->success(CommonResource::collection($data));
    }

    /**
     * @throws HighKerException
     */
    public function pay(VipRequest $request)
    {
        $user = Auth::user();

        // 获取绑定的微信信息
        if (!$accountWechat = AccountBase::getByAccountId($user->account_id, AccountRegisterType::MINI_PROGRAM)) {
            throw new HighKerException('系统错误');
        }

        $slug = $request->input('slug');

        $product = VipProduct::MAP[$slug];

        // 开启事务
        $db = HighKer::db();
        $db->beginTransaction();

        try {
            $vipOrder = VipOrder::create(Auth::id(), VipOrderType::USER, $slug, '用户下单');

            $order = [
                'out_trade_no' => $vipOrder->no,
                'description'  => 'HighKer 会员 '.$product['name'],
                'amount'       => [
                    'total' => $product['price'] * 100,
                ],
                'payer' => [
                    'openid' => $accountWechat->open_id,
                ],
            ];

            $result = Pay::wechat()->mini($order);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();

            Log::info('vip 支付 error');
            Log::info($e);

            throw new HighKerException('系统错误');
        }

        return $this->success(CommonResource::make($result));
    }
}
