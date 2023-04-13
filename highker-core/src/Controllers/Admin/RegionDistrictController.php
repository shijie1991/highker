<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Models\Region;

class RegionDistrictController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '地区管理';

    protected function grid()
    {
        $province = Region::query()->with(['parents'])->where('level', '=', 2)->orderBy('id');

        return Grid::make($province, function (Grid $grid) {
            $grid->disableActions();
            $grid->disableRowSelector();
            // 禁用
            $grid->disableCreateButton();
            // 禁用批量删除按钮
            $grid->disableBatchDelete();

            $grid->column('id', 'ID');
            $grid->column('name', '地区');
            $grid->column('parents.name', '所属城市')->label();
        });
    }
}
