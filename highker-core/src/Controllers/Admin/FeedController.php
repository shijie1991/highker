<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Column\Filter\In;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Tooltip;
use HighKer\Core\Admin\Extensions\Tools\FeedImages;
use HighKer\Core\Admin\Renderable\Feed\Image;
use HighKer\Core\Enum\FeedStatus;
use HighKer\Core\Models\Administrator;
use HighKer\Core\Models\Feed;
use HighKer\Core\Models\Topic;
use HighKer\Core\Models\User;
use HighKer\Core\Notifications\FeedForbiddenNotifications;
use Illuminate\Support\Collection;

class FeedController extends AdminController
{
    protected $title = '动态管理';

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

                    $('.table-main tbody').children().each(function(){
                        var id = $.trim($(this).children("td:first").text());
                        $(this).children(".content").addClass('content-'+id);
                    });
            JS;
    }

    protected function grid(): Grid
    {
        Admin::script($this->script());

        $feed = Feed::query()->withoutGlobalScope('approve')->with(['user', 'topics', 'content'])->orderByDesc('id');

        return Grid::make($feed, function (Grid $grid) {
            $grid->tools(new FeedImages());

            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->fixColumns(2, -3);

            $grid->column('id', 'ID')->sortable();
            $grid->column('user', '发布用户')->userName();
            $grid->column('status', '状态')->filter(In::make(FeedStatus::MAP))
                ->using(FeedStatus::MAP)->dot([
                    FeedStatus::PENDING   => Admin::color()->warning(),
                    FeedStatus::APPROVE   => Admin::color()->success(),
                    FeedStatus::FORBIDDEN => Admin::color()->danger(),
                ]);
            // $grid->column('topics', '话题')->display(function ($topics) {
            //     return $topics->pluck('name')->transform(function ($item) {
            //         return '# '.$item;
            //     });
            // })->label();
            $grid->column('content.text', '内容')->setAttributes(['class' => 'content'])
                ->if(function (Grid\Column $column) {
                    return $column->getValue() !== '';
                })
                ->display(function ($content) {
                    $tooltip = new Tooltip('.content-'.$this->id);

                    $tooltip->title(addslashes(str_replace(PHP_EOL, '', $content)))
                        ->maxWidth(200)
                        ->blue()
                        ->bottom()
                    ;

                    return $content;
                })
                ->limit(10)
                ->else()
                ->display('未填写内容')->label(Admin::color()->danger());

            $grid->column('view_count', '阅读数量')->sortable();
            $grid->column('like_count', '点赞数量')->sortable();
            $grid->column('comment_count', '评论数量')->sortable();
            $grid->column('created_at', '创建时间');

            $grid->column('image', '图片')->display('点击查看')->expand(Image::make());
            // $grid->column('is_top', '置顶')->filter(In::make([1 => '置顶', 0 => '未置顶']))->switch();

            $grid->column('action', '操作')->display(function () {
                /* @noinspection PhpUndefinedFieldInspection */
                return $this->status;
            })->radio(FeedStatus::MAP, true);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', '动态 ID');
                $filter->equal('user_id', '用户 ID');
            });
        });
    }

    protected function form(): Form
    {
        $feed = Feed::query()->withoutGlobalScope('approve');

        return Form::make($feed, function (Form $form) {
            // 第一列占据1/2的页面宽度
            $form->column(6, function (Form $form) {
                $form->select('user_id', '用户')->required()->options(function ($id) {
                    $user = User::query()->find($id);

                    if ($user) {
                        return [$user->id => $user->name];
                    }
                })->ajax('api/users');
                $form->select('topic_id', '圈子')->required()->options($this->getTopicList());
                $form->radio('status', '状态')->options(FeedStatus::MAP);
                $form->switch('is_top', '是否置顶');
                $form->textarea('content', '内容')->required()->rows(10)->rules('required');
            });

            // 第一列占据1/2的页面宽度
            $form->column(6, function (Form $form) {
                $form->hasMany('images', '图片', function (Form\NestedForm $form) {
                    $form->image('image', '图片')->required();
                })->useTable();
            });

            $form->saving(function (Form $form) {
                if ($form->isCreating()) {
                    $form->deleteInput('action');
                }

                if ($form->input('action')) {
                    /* @noinspection PhpUndefinedFieldInspection */
                    $form->status = $form->input('action');

                    if ($form->status == FeedStatus::FORBIDDEN) {
                        $form->model()->user->notify(new FeedForbiddenNotifications(Administrator::query()->find(1), '您发布的动态因违反社区规定审核未通过，已被屏蔽。'));
                    }

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
