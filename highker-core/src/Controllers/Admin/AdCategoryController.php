<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Models\AdCategory;

class AdCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '广告位管理';

    protected function grid()
    {
        return Grid::make(new AdCategory(), function (Grid $grid) {
            $grid->disableViewButton();
            $grid->disableDeleteButton();

            $grid->model()->orderByDesc('id');
            $grid->column('name', '名称');
            $grid->column('width', '宽度');
            $grid->column('height', '高度');
            $grid->column('status', '是否启用')->switch();
            $grid->column('created_at', '创建时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name', '广告位名称');
                $filter->equal('status', '是否开启')->select([1 => '启用', 0 => '关闭']);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $id = $actions->getKey();
                // append一个操作
                $actions->append("<a href='ad?category_id={$id}'><i></i>查看广告</a>");
                // prepend一个操作
                $actions->append("<a href='ad/create?category_id={$id}'><i></i>添加广告</a>");
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new AdCategory(), function (Form $form) {
            $form->display('id', 'ID');
            $form->text('name', '名称')->rules('required', ['required' => '请填写名称']);
            $form->text('width', '宽度')->rules('required|numeric|min:0', ['required' => '请填写宽度', 'numeric' => '请输入数字']);
            $form->text('height', '高度')->rules('required|numeric|min:0', ['required' => '请填写高度', 'numeric' => '请输入数字']);
            $form->switch('status', '是否启用');
        });
    }
}
