<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Models\Region;

class RegionCityController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '城市管理';

    protected function grid()
    {
        $province = Region::query()->with(['children'])->where('level', '=', 1)->orderBy('id');

        return Grid::make($province, function (Grid $grid) {
            $grid->disableActions();
            $grid->disableRowSelector();
            // 禁用
            $grid->disableCreateButton();
            // 禁用批量删除按钮
            $grid->disableBatchDelete();

            $grid->column('id', 'ID');
            $grid->column('name', '城市');
            $grid->column('children', '地区')->pluck('name')->label();
        });
    }
}
