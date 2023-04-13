<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Enum\ChatMessageType;
use HighKer\Core\Enum\UserGender;
use HighKer\Core\Models\ChatMessage;

class ChatMessageController extends AdminController
{
    protected $title = '消息管理';

    protected $description = [
        'index' => '消息列表',
    ];

    /** @noinspection PhpUndefinedFieldInspection */
    protected function grid(): Grid
    {
        return Grid::make(new ChatMessage(), function (Grid $grid) {
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableActions();
            $grid->disableCreateButton();

            $grid->model()->with(['sender_user'])->orderByDesc('id');
            $grid->column('id', 'ID')->sortable();
            // $grid->column('sender_user', '发送用户')->userName();
            $grid->column('sender_user', '发送用户')->display(function ($senderUser) {
                /* @noinspection PhpUndefinedFieldInspection */
                $color = match ($senderUser->gender) {
                    UserGender::MALE   => Admin::color()->blue(),
                    UserGender::FEMALE => Admin::color()->pink(),
                    default            => Admin::color()->orange2(),
                };

                return "<i class=\"fa fa-circle\" style=\"font-size: 13px;color:{$color}\"></i>&nbsp&nbsp{$senderUser->name}";
            });
            $grid->column('content', '内容')
                // 如果是文字
                ->if(function () {
                    return $this->type == ChatMessageType::TEXT;
                })->limit(50)

                // 如果是图片
                ->if(function () {
                    return $this->type == ChatMessageType::IMAGE;
                })->image()

                // 如果是语音
                ->if(function () {
                    return $this->type == ChatMessageType::VOICE;
                })->audio();

            $grid->column('created_at', '创建时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('conversation_id', '对话 ID');
                $filter->equal('sender', '发送用户 ID');
                $filter->in('type', '消息类型')->multipleSelect(ChatMessageType::MAP);
            });
        });
    }
}
