<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Admin\Forms;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use HighKer\Core\Enum\VipOrderType;
use HighKer\Core\Enum\VipProduct;
use HighKer\Core\Models\User;
use HighKer\Core\Models\VipOrder;

class GiftVip extends Form implements LazyRenderable
{
    use LazyWidget;

    /**
     * Handle the form request.
     */
    public function handle(array $input)
    {
        // 获取外部传递参数
        $userId = $this->payload['user_id'] ?? null;

        if (!$userId) {
            return $this->response()->error('系统错误')->refresh();
        }

        $user = User::query()->find($userId);

        $vipOrder = VipOrder::create($user->id, VipOrderType::SYSTEM, $input['slug'], $input['remark']);
        if ($vipOrder) {
            $product = VipProduct::MAP[$input['slug']];

            // 更新 用户 VIP 信息
            $user->is_vip = true;
            $user->vip_expired_at = now()->parse($user->vip_expired_at)->setTime(0, 0)->addMonths($product['moon']);
            $user->save();

            // 更新 订单信息
            $vipOrder->closed = true;
            $vipOrder->payment_at = now();
            $vipOrder->save();
        }

        return $this->response()->success('赠送成功')->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $vipOptions = collect(VipProduct::MAP)->pluck('name', 'slug');
        $this->radio('slug', '赠送时长')->options($vipOptions)->required();
        $this->textarea('remark', '备注')->required()->help('必须填写备注');
    }

    /**
     * @return array
     */
    public function default()
    {
        return [
            'slug'   => 1,
            'remark' => '系统赠送',
        ];
    }
}
