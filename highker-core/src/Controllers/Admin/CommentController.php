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
use HighKer\Core\Admin\Extensions\Tools\CommentImages;
use HighKer\Core\Admin\Renderable\Comment\Image;
use HighKer\Core\Enum\CommentLevel;
use HighKer\Core\Enum\CommentStatus;
use HighKer\Core\Models\Comment;
use HighKer\Core\Notifications\CommentForbiddenNotifications;

class CommentController extends AdminController
{
    protected $title = '评论管理';

    protected $description = [
        'index' => '列表模式',
    ];

    public function script(): string
    {
        /* @noinspection JSUnresolvedVariable */
        return <<<'JS'
                    const app = $('#app');

                    app.off('click').on("click",'.preview_image', function(){
                      return Dcat.helpers.previewImage($(this).attr('src'));
                    });
            JS;
    }

    protected function grid(): Grid
    {
        Admin::script($this->script());

        $comment = Comment::query()->withoutGlobalScope('approve')->with(['content', 'user'])->orderByDesc('id');

        return Grid::make($comment, function (Grid $grid) {
            $grid->tools(new CommentImages());

            $grid->quickSearch('content')->placeholder('搜索评论内容...');

            $grid->fixColumns(2, -6);

            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableActions();

            $grid->column('id', 'ID')->sortable();
            $grid->column('user', '评论用户')->userName();
            $grid->column('content.text', '评论内容');
            $grid->column('image', '图片')->display('点击查看')->expand(Image::make());
            $grid->column('like_count', '点赞数量')->sortable();
            $grid->column('reply_count', '回复数量')->sortable();
            $grid->column('level', '评论类型')->filter(
                Grid\Column\Filter\In::make(CommentLevel::MAP)
            )->using(CommentLevel::MAP)->label([
                CommentLevel::COMMENT      => Admin::color()->green(),
                CommentLevel::REPLY        => Admin::color()->indigo(),
                CommentLevel::SECOND_REPLY => Admin::color()->info(),
            ]);

            $grid->column('status', '屏蔽')->filter(
                Grid\Column\Filter\In::make(CommentStatus::MAP)
            )->switch();
            $grid->column('created_at', '创建时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('feed_id', '动态 ID');
                $filter->equal('user_id', '用户 ID');
                $filter->between('created_at', '发布时间')->datetime();
            });
        });
    }

    protected function form(): Form
    {
        return Form::make(new Comment(), function (Form $form) {
            $form->radio('status', '屏蔽')->options(CommentStatus::MAP);

            $form->saved(function (Form $form, $result) {
                if (!$form->isCreating()) {
                    // 消息通知
                    $form->model()->user->notify(new CommentForbiddenNotifications(Admin::user(), $form->model()));
                }
            });
        });
    }
}
