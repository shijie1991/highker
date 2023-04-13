<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Enum\VipOrderType;
use HighKer\Core\Models\VipOrder;
use Illuminate\Database\Eloquent\Builder;

class VipController extends AdminController
{
    protected $title = '会员管理';

    protected function grid()
    {
        $vipOrder = VipOrder::query()->with(['user'])
            ->withCount([
                // 获取所有订单数量
                'orders as order_all_count',
                // 获取用户下单数量
                'orders as order_user_count' => function (Builder $query) {
                    $query->where('type', VipOrderType::USER);
                },
                // 获取系统赠送数量
                'orders as order_system_count' => function (Builder $query) {
                    $query->where('type', VipOrderType::SYSTEM);
                },
                // 获取用户支付成功数量
                'orders as order_user_pay_count' => function (Builder $query) {
                    $query->where('type', VipOrderType::USER)->whereNotNull('payment_no');
                },
            ])
            ->withSum(['orders as orders_sum_amount' => function (Builder $query) {
                $query->where('type', VipOrderType::USER)->whereNotNull('payment_no');
            }], 'amount')

            ->groupBy('user_id')
            ->whereNotNull('payment_at')
            ->orderByDesc('id')
        ;

        return Grid::make($vipOrder, function (Grid $grid) {
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableEditButton();
            $grid->disableActions();

            $grid->column('id', 'ID')->sortable();
            $grid->column('user_id', '会员 ID');
            $grid->column('user_object', '会员')->userName();

            $grid->column('pay_time', '最后下单时间')->display(function ($value) {
                return $this->created_at;
            })->width('100px');

            $grid->column('user.vip_expired_at', '到期时间')->width('100px')
                ->if(function (Grid\Column $column) {
                    return is_null($this->user->vip_expired_at);
                })
                ->then(function (Grid\Column $column) {
                    $column->display('已到期')->label(Admin::color()->gray());
                })
                ->else(function (Grid\Column $column) {
                    $column->display($column->getOriginal());
                })
            ;
            $grid->column('description', '最后开通时长');
            $grid->column('vip_status', 'VIP剩余时长')
                ->if(function (Grid\Column $column) {
                    return is_null($this->user->vip_expired_at);
                })
                ->then(function (Grid\Column $column) {
                    $column->display('已到期')->label(Admin::color()->gray());
                })
                ->else(function (Grid\Column $column) {
                    $day = now()->diffInDays($this->user->vip_expired_at);
                    if ($day > 10) {
                        $column->display($day.' 天')->label(Admin::color()->success());
                    } else {
                        $column->display($day.' 天')->label(Admin::color()->red());
                    }
                })
            ;

            $grid->column('order_all_count', '订单数量');
            $grid->column('order_system_count', '赠送数量');
            $grid->column('order_user_count', '用户下单数量');
            $grid->column('order_user_pay_count', '消费次数');
            $grid->column('orders_sum_amount', '消费总额')
                ->if(function (Grid\Column $column) {
                    return is_null($this->orders_sum_amount);
                })
                ->then(function (Grid\Column $column) {
                    $column->display(0);
                })
                ->else(function (Grid\Column $column) {
                    $column->display($column->getOriginal());
                })
            ;

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->equal('user_id', '用户 ID')->width(2);
                $filter->equal('user.is_vip', 'VIP 是否到期')->select([0 => '已过期', 1 => '未过期'])->width(2);
            });
        });
    }
}
