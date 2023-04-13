<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Column\Filter\In;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Enum\VipOrderType;
use HighKer\Core\Models\VipOrder;

class VipOrderController extends AdminController
{
    protected $title = '订单管理';

    protected function grid()
    {
        $vipOrder = VipOrder::query()->with(['user'])->orderByDesc('id');

        return Grid::make($vipOrder, function (Grid $grid) {
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableEditButton();
            $grid->disableActions();

            $grid->column('id', 'ID')->sortable();
            $grid->column('user', '下单用户')->userName();
            $grid->column('no', '订单号');
            $grid->column('type', '订单类型')->filter(In::make(VipOrderType::MAP))
                ->using(VipOrderType::MAP)->dot([
                    VipOrderType::USER   => Admin::color()->success(),
                    VipOrderType::SYSTEM => Admin::color()->danger(),
                ]);
            $grid->column('payment_no', '支付订单号');
            $grid->column('amount', '金额');
            $grid->column('description', '描述');
            $grid->column('closed', '订单是否关闭')->filter(In::make([0 => '未关闭', 1 => '已关闭']))
                ->using([0 => '未关闭', 1 => '已关闭'])->dot([
                    0 => Admin::color()->danger(),
                    1 => Admin::color()->success(),
                ]);
            $grid->column('remark', '备注');
            $grid->column('payment_at', '支付时间')->width('100px');
            $grid->column('updated_at', '更新时间')->width('100px');
            $grid->column('created_at', '创建时间')->width('100px');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('user_id', '用户 ID');
                $filter->expand(false);
            });
        });
    }
}
