<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Card;
use HighKer\Core\Models\Topic;
use HighKer\Core\Models\TopicGroup;

class FeedTopicController extends AdminController
{
    protected $title = '话题管理';

    /** @noinspection PhpParamsInspection */
    protected function grid()
    {
        return Grid::make(new Topic(), function (Grid $grid) {
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->model()->with(['group'])->orderByDesc('id');

            $grid->column('id', 'ID');
            $grid->column('cover', '封面')->image('', 50, 50);
            $grid->column('name', '名称');
            $grid->column('group.name', '所属分组')->if(function (Grid\Column $column) {
                return $column->getValue() == null;
            })->display('未选择分组')->label(Admin::color()->danger());

            $grid->column('description', '介绍')->display('点击查看')->expand(function () {
                $card = new Card(null, nl2br($this->description));

                return "<div style='padding:10px 10px 0;'>{$card}</div>";
            });
            $grid->column('follow_count', '关注数量');
            $grid->column('feed_count', '动态数量');
            $grid->column('created_at', '创建时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', '话题 ID');
                $filter->like('name', '话题名称');
            });
        });
    }

    /**
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Topic(), function (Form $form) {
            $form->text('name', '话题名称')->rules('required');
            $form->select('group_id', '选择分组')->options(TopicGroup::query()->where('id', '>', 2)->pluck('name', 'id'));
            // $form->image('cover', '话题封面')->maxSize(3072)->uniqueName()->autoUpload()->rules('required|image');
            $form->textarea('description', '话题介绍')->rows(10);
        });
    }
}
