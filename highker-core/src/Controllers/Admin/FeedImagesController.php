<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Admin\Extensions\Tools\FeedImages;
use HighKer\Core\Enum\FeedImageStatus;
use HighKer\Core\Models\FeedImage;
use HighKer\Core\Models\Topic;
use Illuminate\Support\Collection;

class FeedImagesController extends AdminController
{
    protected $title = '动态管理';

    protected $description = [
        'index' => '图片模式',
    ];

    protected function grid()
    {
        return Grid::make(FeedImage::query()->withoutGlobalScope('approve'), function (Grid $grid) {
            $grid->view('admin.grid.feed.images');

            $grid->paginate(21);

            $grid->tools(new FeedImages());

            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableActions();
            $grid->model()->with(['user', 'feed'])->orderByDesc('id');

            $grid->column('id', '图片 ID')->sortable();
            $grid->column('user', '发布用户')->userName();
            $grid->column('feed.id', '动态 ID');
            $grid->column('path', '图片')->image();
            $grid->column('action', '操作')->display(function () {
                /* @noinspection PhpUndefinedFieldInspection */
                return $this->status;
            })->radio(FeedImageStatus::MAP, true);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', '动态 ID');
                $filter->equal('user_id', '用户 ID');
                $filter->in('feed.topic_id', '所属圈子')->multipleSelect($this->getTopicList());
            });
        });
    }

    protected function form(): Form
    {
        return Form::make(FeedImage::query()->withoutGlobalScope('approve'), function (Form $form) {
            $form->hidden('status', '状态');

            $form->saving(function (Form $form) {
                if ($form->input('action')) {
                    /* @noinspection PhpUndefinedFieldInspection */
                    $form->status = $form->input('action');

                    // 删除、忽略用户提交的数据
                    $form->deleteInput('action');
                }
            });
        });
    }

    protected function getTopicList(): Collection
    {
        $topicList = Topic::query()->get(['id', 'name'])->pluck('name', 'id');
        $topicList->prepend('未选择', 0);

        return $topicList;
    }
}
