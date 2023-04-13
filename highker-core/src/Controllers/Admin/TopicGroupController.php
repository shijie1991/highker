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
use HighKer\Core\Models\TopicGroup;

class TopicGroupController extends AdminController
{
    protected $title = '话题分组';

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
                    $form->text('name', '分组名称')->rules('required');
                    $column->append((new Box('添加分组', $form)));
                });
            })
        ;
    }

    /**
     * @return Form
     */
    public function form()
    {
        return Form::make(new TopicGroup(), function (Form $form) {
            $form->display('id', 'ID');
            $form->text('name', '分组名称')->rules('required');
        });
    }

    /**
     * @return Tree
     */
    protected function treeView()
    {
        return new Tree(new TopicGroup(), function (Tree $tree) {
            $tree->disableCreateButton();
            $tree->disableQuickCreateButton();
            $tree->disableEditButton();

            $tree->branch(function ($branch) {
                return "<i class='fa'></i>&nbsp;<strong>{$branch['name']}</strong>";
            });
        });
    }
}
