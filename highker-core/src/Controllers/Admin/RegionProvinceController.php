<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Models\Region;

class RegionProvinceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '省份管理';

    protected function grid()
    {
        $province = Region::query()->with(['children'])->where('level', '=', 0)->orderBy('id');

        return Grid::make($province, function (Grid $grid) {
            $grid->disableActions();
            $grid->disableRowSelector();
            // 禁用
            $grid->disableCreateButton();
            // 禁用批量删除按钮
            $grid->disableBatchDelete();
            // 禁用分页
            $grid->disablePagination();
            // 默认为每页20条
            $grid->paginate(35);

            $grid->column('id', 'ID');
            $grid->column('name', '省份');
            $grid->column('children', '城市')->pluck('name')->label();
        });
    }
}
