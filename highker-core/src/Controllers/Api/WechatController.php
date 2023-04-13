<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use EasyWeChat\Kernel\Exceptions\BadRequestException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use HighKer\Core\Enum\VipProduct;
use HighKer\Core\Models\User;
use HighKer\Core\Models\VipOrder;
use HighKer\Core\Support\Facades\Wechat;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Support\Wechat\Handler\MediaCheckHandler;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use Throwable;
use Yansongda\LaravelPay\Facades\Pay;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;

class WechatController extends BaseController
{
    /**
     * @return ResponseInterface
     *
     * @throws BadRequestException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws Throwable
     */
    public function serve()
    {
        $server = Wechat::miniApp()->getServer();

        // 微信小程序 音视频内容安全识别 回调
        $server->addEventListener('wxa_media_check', MediaCheckHandler::class);

        return $server->serve();
    }

    /**
     * @throws InvalidParamsException
     * @throws ContainerException|Throwable
     */
    public function notify()
    {
        $result = Pay::wechat()->callback();

        $no = $result['resource']['ciphertext']['out_trade_no'];
        $transactionId = $result['resource']['ciphertext']['transaction_id'];

        $vipOrder = VipOrder::query()->where('no', $no)->where('closed', 0)->first();

        if ($vipOrder) {
            $user = User::query()->find($vipOrder->user_id);

            $product = VipProduct::MAP[$vipOrder->vip_slug];

            // 开启事务
            $db = HighKer::db();
            $db->beginTransaction();

            try {
                // 更新 用户 VIP 信息
                $user->is_vip = true;
                $user->vip_expired_at = now()->parse($user->vip_expired_at)->setTime(0, 0)->addMonths($product['moon']);
                $user->save();

                // 更新 订单信息
                $vipOrder->closed = true;
                $vipOrder->payment_no = $transactionId;
                $vipOrder->payment_at = now();
                $vipOrder->save();

                $db->commit();

                return Pay::wechat()->success();
            } catch (Throwable $e) {
                $db->rollBack();

                Log::info('vip 支付 回调错误');
                Log::info($e);
            }
        }
    }
}
