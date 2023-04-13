<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Doctrine\DBAL\Query\QueryBuilder;
use HighKer\Core\Models\Ad;
use HighKer\Core\Models\AdCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

/**
 * Class AdController.
 */
class AdController extends AdminController
{
    protected $title = '广告管理';

    protected function grid(): Grid
    {
        $Ad = Ad::query()->with(['category'])->orderByDesc('id');

        return Grid::make($Ad, function (Grid $grid) {
            $grid->disableViewButton();
            $grid->disableDeleteButton();

            $grid->column('image', '广告图')->image('', 50, 50);
            $grid->column('name', '广告名称');
            $grid->column('category.name', '所属广告位');
            $grid->column('url', '广告地址')->link();
            $grid->column('status', '是否启用')->switch();
            $grid->column('target', '新窗口打开')->switch();
            $grid->column('before', '开始时间');
            $grid->column('after', '结束时间');
            $grid->column('created_at', '创建时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name', '广告名称');
                $filter->equal('category_id', '广告位')->select($this->getAdCategoryList());
                $filter->equal('status', '是否启用')->select([1 => '启用', 0 => '关闭']);
            });
        });
    }

    protected function form(): Form
    {
        $categoryId = Request::input('category_id');

        return Form::make(new Ad(), function (Form $form) use ($categoryId) {
            $form->row(function (Form\Row $form) use ($categoryId) {
                $form->width(6)->text('name', '广告名称')->rules('required');
                $form->width(6)->datetime('before', '开始时间');
                $form->width(6)->select('category_id', '选择广告位')->options($this->getAdCategoryList())->default($categoryId);
                $form->width(6)->datetime('after', '结束时间');
                $form->width(6)->text('url', '广告链接')->rules('required|url');
                $form->width(6)->switch('status', '是否启用')->default(1);
                $form->width(6)->image('image', '封面图片')->rules('required|image');
                $form->width(6)->switch('target', '新窗口打开')->default(1);
            });
        });
    }

    /**
     * 按格式 返回 广告位.
     *
     * @param null $status
     */
    protected function getAdCategoryList($status = null): Collection
    {
        $query = AdCategory::query();
        $query->when(!is_null($status), function ($query) use ($status) {
            /* @var QueryBuilder $query */
            return $query->where('status', $status);
        });

        return $query->get(['id', 'name'])->pluck('name', 'id');
    }
}
