<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Enum\UserGender;
use HighKer\Core\Models\ChatConversation;

class ChatConversationController extends AdminController
{
    /**
     * @var string
     */
    protected $title = '对话管理';

    /**
     * @var string[]
     */
    protected $description = [
        'index' => '对话列表',
    ];

    protected function grid(): Grid
    {
        return Grid::make(new ChatConversation(), function (Grid $grid) {
            // 在控制器中切换 操作按钮显示方式
            $grid->setActionClass(Grid\Displayers\Actions::class);

            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableCreateButton();

            $grid->disableEditButton();

            $grid->model()->with(['sender_user', 'receiver_user'])->orderByDesc('id');
            $grid->column('id', 'ID')->sortable();
            $grid->column('sender_user', '发送用户')->display(function ($senderUser) {
                /* @noinspection PhpUndefinedFieldInspection */
                $color = match ($senderUser->gender) {
                    UserGender::MALE   => Admin::color()->blue(),
                    UserGender::FEMALE => Admin::color()->pink(),
                    default            => Admin::color()->orange2(),
                };

                return "<i class=\"fa fa-circle\" style=\"font-size: 13px;color:{$color}\"></i>&nbsp&nbsp{$senderUser->name}";
            });
            $grid->column('receiver_user', '接收用户')->display(function ($receiverUser) {
                /* @noinspection PhpUndefinedFieldInspection */
                $color = match ($receiverUser->gender) {
                    UserGender::MALE   => Admin::color()->blue(),
                    UserGender::FEMALE => Admin::color()->pink(),
                    default            => Admin::color()->orange2(),
                };

                return "<i class=\"fa fa-circle\" style=\"font-size: 13px;color:{$color}\"></i>&nbsp&nbsp{$receiverUser->name}";
            });
            // $grid->column('sender_user', '发送用户')->userName();
            // $grid->column('receiver_user', '接收用户')->userName();
            $grid->column('private', '对话类型')->display(function () {
                /* @noinspection PhpUndefinedFieldInspection */
                return $this->private ? 1 : 0;
            })->filter(
                Grid\Column\Filter\In::make([0 => '盲盒', 1 => '私信'])
            )->using([0 => '盲盒', 1 => '私信'])->label([
                0 => Admin::color()->primary(),
                1 => Admin::color()->success(),
            ]);

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $id = $actions->getKey();
                // append一个操作
                $actions->append("<a href='messages?conversation_id={$id}'><i></i>查看对话</a>");
            });

            $grid->column('created_at', '创建时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', '对话 ID');
                $filter->equal('sender', '发送用户 ID');
                $filter->equal('receiver', '接收用户 ID');
                $filter->in('private', '对话类型')->multipleSelect([0 => '盲盒', 1 => '私信']);
            });
        });
    }
}
