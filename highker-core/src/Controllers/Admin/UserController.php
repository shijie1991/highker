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
use HighKer\Core\Admin\Actions\Grid\GiftVipAction;
use HighKer\Core\Enum\UserGender;
use HighKer\Core\Enum\UserStatus;
use HighKer\Core\Models\Administrator;
use HighKer\Core\Models\User;
use HighKer\Core\Notifications\UserInfoResetNotifications;

class UserController extends AdminController
{
    protected $title = '用户管理';

    protected function grid()
    {
        $user = User::query()->with(['info'])->orderByDesc('id');

        return Grid::make($user, function (Grid $grid) {
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableEditButton();
            $grid->showQuickEditButton();

            $grid->actions([new GiftVipAction()]);

            $grid->column('id', 'User ID')->sortable();
            $grid->column('avatar', '头像')->image('', 50, 50);
            $grid->column('name', '昵称')->display(function ($name) {
                /* @noinspection PhpUndefinedFieldInspection */
                $color = match ($this->gender) {
                    UserGender::MALE   => Admin::color()->blue(),
                    UserGender::FEMALE => Admin::color()->pink(),
                    default            => Admin::color()->orange2(),
                };

                return "<i class=\"fa fa-circle\" style=\"font-size: 13px;color:{$color}\"></i>&nbsp&nbsp{$name}";
            });
            $grid->column('is_vip', '是否开通 VIP')->filter(
                Grid\Column\Filter\In::make([0 => '未开通', 1 => '已开通'])
            )->using([0 => '未开通', 1 => '已开通'])->label([
                0 => Admin::color()->custom(),
                1 => Admin::color()->red(),
            ]);
            $grid->column('status', '用户状态')->filter(
                Grid\Column\Filter\In::make(UserStatus::MAP)
            )->using(UserStatus::MAP)->label([
                UserStatus::NORMAL    => Admin::color()->success(),
                UserStatus::LOCKED    => Admin::color()->orange(),
                UserStatus::FORBIDDEN => Admin::color()->danger(),
            ]);
            $grid->column('score', '积分')->display(function ($value) {
                return $value;
            })->sortable();
            $grid->column('exp', '经验')->display(function ($value) {
                return $value;
            })->sortable();
            $grid->column('info.follow_count', '关注数量');
            $grid->column('info.fans_count', '粉丝数量');
            $grid->column('info.description', '个性签名')->display('点击查看')->expand(function () {
                /** @noinspection PhpUndefinedFieldInspection */
                $card = new Card(null, nl2br($this->info['description']));

                return "<div style='padding:10px 10px 0;'>{$card}</div>";
            });
            $grid->column('updated_at', '更新时间')->display(function ($value) {
                return $value;
            })->width('100px');
            $grid->column('created_at', '注册时间')->display(function ($value) {
                return $value;
            })->width('100px');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', 'User ID');
                $filter->like('name', '用户昵称');
                $filter->in('status', '用户状态')->multipleSelect(UserStatus::MAP);
                $filter->expand(false);
            });
        });
    }

    protected function form()
    {
        Admin::script($this->RestNameScript());

        $user = User::query()->with(['info']);

        return Form::make($user, function (Form $form) {
            $form->model()->makeVisible(['created_at', 'updated_at']);
            $form->display('id');

            $form->display('', '昵称')->value($form->model()->name)->addElementClass('edit_name_text');
            $form->hidden('name')->addElementClass('edit_name')->value($form->model()->name);
            $form->html("<button class='rest_name btn btn-outline-warning'>重置昵称</button>", $label = '');
            $form->radio('gender', '性别')->options(collect(UserGender::MAP)->except(0))->help('选择请注意');
            $form->textarea('info.description', '个性签名');

            $form->display('created_at');
            $form->display('updated_at');

            $form->saved(function (Form $form) {
                // 判断是否是新增操作
                if (!$form->isCreating()) {
                    // 如果修改了昵称
                    if ($form->model()->wasChanged('name')) {
                        if ($form->name == '昵称已重置') {
                            $form->model()->notify(new UserInfoResetNotifications(Administrator::query()->find(1), '您设置的昵称因违反社区规定，已被重置。'));
                        }
                    }
                }
            });
        });
    }

    public function RestNameScript(): string
    {
        /* @noinspection JSUnresolvedVariable */
        return <<<'JS'
                Dcat.ready(function () {
                    $(document).off('click', '.rest_name').on('click', '.rest_name', function () {
                        $('.edit_name').val('昵称已重置');
                        $('.edit_name_text').html('昵称已重置');
                    })
                });
            JS;
    }
}
