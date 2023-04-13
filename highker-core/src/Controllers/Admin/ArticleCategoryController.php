<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Form;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Tree;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Form as WidgetForm;
use HighKer\Core\Models\ArticleCategory;

/**
 * Class ArticleCategoryController.
 */
class ArticleCategoryController extends AdminController
{
    protected $title = '文章分类';

    /**
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header($this->title)
            ->body(function (Row $row) {
                $row->column(7, $this->treeView()->render());
                $row->column(5, function (Column $column) {
                    $form = new WidgetForm();
                    $form->select('parent_id', '上级')->options(ArticleCategory::selectOptions());
                    $form->text('name', '分类名称')->rules('required');
                    $column->append((new Box('添加文章分类', $form)));
                });
            })
        ;
    }

    /**
     * @return Form
     */
    public function form()
    {
        return Form::make(new ArticleCategory(), function (Form $form) {
            $form->display('id', 'ID');
            $form->select('parent_id', '上级')->options(ArticleCategory::selectOptions());
            $form->text('name', '分类名称')->rules('required');
        });
    }

    /**
     * @return Tree
     */
    protected function treeView()
    {
        return new Tree(new ArticleCategory(), function (Tree $tree) {
            $tree->disableCreateButton();
            $tree->disableQuickCreateButton();
            $tree->disableEditButton();

            $tree->branch(function ($branch) {
                return "<i class='fa'></i>&nbsp;<strong>{$branch['name']}</strong>";
            });
        });
    }
}
