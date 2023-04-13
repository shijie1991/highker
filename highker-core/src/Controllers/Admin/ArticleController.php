<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use HighKer\Core\Enum\CommentStatus;
use HighKer\Core\Models\Article;
use HighKer\Core\Models\ArticleCategory;

class ArticleController extends AdminController
{
    protected $title = '文章分类';

    public function create(Content $content)
    {
        return $content
            ->title('文章')
            ->description('创建')
            ->body($this->form())
        ;
    }

    protected function grid(): Grid
    {
        return Grid::make(new Article(), function (Grid $grid) {
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->fixColumns(2, -3);

            $grid->model()->with(['category'])->orderByDesc('id');

            $grid->column('id', 'ID')->sortable();
            $grid->column('category.name', '分类');
            $grid->column('title', '标题');
        });
    }

    protected function form()
    {
        return Form::make(new Article(), function (Form $form) {
            $form->block(4, function (Form\BlockForm $form) {
                $form->title('基本');
                $form->text('title', '标题')->rules('required', ['required' => '请输入标题']);
                $form->select('category_id', '文章分类')
                    ->options(ArticleCategory::selectOptions(null, '请选择'))
                    ->rules('required|not_in:0', ['required' => '请选择分类', 'not_in' => '请选择分类'])
                ;
                $form->radio('status', '状态')->options(CommentStatus::MAP)->default(CommentStatus::APPROVE);
            });
            $form->block(8, function (Form\BlockForm $form) {
                // 设置标题
                $form->title('内容');
                // 显示底部提交按钮
                $form->showFooter();
                $form->editor('content', '内容')->rules('required', ['required' => '请输入内容']);
            });
        });
    }
}
