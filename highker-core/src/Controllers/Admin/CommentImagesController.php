<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Admin\Extensions\Tools\CommentImages;
use HighKer\Core\Models\CommentImage;

class CommentImagesController extends AdminController
{
    protected $title = '评论管理';

    protected $description = [
        'index' => '图片模式',
    ];

    protected function grid()
    {
        return Grid::make(new CommentImage(), function (Grid $grid) {
            $grid->view('admin.grid.comment.images');

            $grid->paginate(21);

            $grid->tools(new CommentImages());

            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableActions();

            $grid->model()->with(['user', 'comment'])->orderByDesc('id');

            $grid->column('id', '图片 ID');
            $grid->column('comment.id', '评论 ID');
            $grid->column('user', '评论用户')->userName();
            $grid->column('path', '图片')->image();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('feed_id', '动态 ID');
                $filter->equal('user_id', '用户 ID');
                $filter->between('created_at', '发布时间')->datetime();
            });
        });
    }
}
