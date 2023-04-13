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
use HighKer\Core\Enum\NoticeEvent;
use HighKer\Core\Enum\NoticeType;
use HighKer\Core\Models\Administrator;
use HighKer\Core\Models\User;
use HighKer\Core\Notifications\SystemNotifications;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;

class NotificationsController extends AdminController
{
    protected $title = '系统通知';

    protected function grid()
    {
        $notifications = DatabaseNotification::query()
            ->where('notice_type', NoticeType::SYSTEM)
            ->where('event', NoticeEvent::SYSTEM_NOTICE)
            ->orderByDesc('created_at')
        ;

        return Grid::make($notifications, function (Grid $grid) {
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableActions();
            $grid->fixColumns(2);

            $grid->model()->with(['notifiable'])->orderByDesc('id');

            $grid->column('id', 'ID');
            $grid->column('data.resource.body.content', '内容')->limit(50);
            $grid->column('notifiable', '通知用户')->userName();
            $grid->column('created_at', '创建时间')->display(function ($value) {
                return Carbon::parse($value)->toDateTimeString();
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', 'ID');
                $filter->equal('notifiable_id', '用户 ID');
                $filter->scope('notifiable_id', '是否群发')->whereNull('notifiable_id');
                $filter->between('created_at', '发布时间')->datetime();
            });
        });
    }

    protected function form(): Form
    {
        return Form::make(DatabaseNotification::class, function (Form $form) {
            $form->select('user_id', '用户')->placeholder('选择用户')->help('不选择将会发送全员通知')
                ->options(function ($id) {
                    $user = User::query()->find($id);

                    if ($user) {
                        return [$user->id => $user->name];
                    }
                })->ajax(route('ajax.users'));
            $form->textarea('content', '内容')->required()->rows(10)->rules('required');

            $form->submitted(function (Form $form) {
                $userId = $form->input('user_id');
                $content = $form->input('content');

                if ($userId) {
                    // 发送异步 通知
                    User::query()->find($userId)->notify(new SystemNotifications(Administrator::query()->find(1), $content));
                } else {
                    // 发送同步 通知
                    Admin::user()->notifyNow(new SystemNotifications(Administrator::query()->find(1), $content));
                }

                // 中断后续逻辑
                return $form->response()->success('发送成功,请稍后查看')->redirect('notifications');
            });
        });
    }
}
