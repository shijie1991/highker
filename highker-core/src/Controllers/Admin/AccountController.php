<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Displayers\Modal;
use Dcat\Admin\Http\Controllers\AdminController;
use HighKer\Core\Admin\Renderable\Account\Phone;
use HighKer\Core\Admin\Renderable\Account\Wechat;
use HighKer\Core\Enum\AccountRegisterType;
use HighKer\Core\Enum\ClientType;
use HighKer\Core\Models\Account;

class AccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '账户管理';

    protected function grid()
    {
        $account = Account::with(['user'])->orderByDesc('id');

        return Grid::make($account, function (Grid $grid) {
            $grid->disableCreateButton();
            $grid->disableActions();

            $grid->column('id', '账户 ID');
            $grid->column('user.name', '名称');
            $grid->column('register_type', '注册方式')->using(AccountRegisterType::MAP)->label([
                AccountRegisterType::WECHAT => Admin::color()->green(),
                AccountRegisterType::PHONE  => Admin::color()->info(),
                AccountRegisterType::FAKER  => Admin::color()->dark(),
            ]);
            $grid->column('register_client_type', '注册客户端')->using(ClientType::MAP)->label([
                ClientType::PC      => 'danger',
                ClientType::ANDROID => 'warning',
                ClientType::IOS     => 'info',
                ClientType::H5      => 'success',
                ClientType::WECHAT  => 'primary',
            ]);
            $grid->column('login_count', '登陆次数')->display(function ($value) {
                return $value ? $value : '0 ';
            })->label();
            $grid->column('register_ip', '注册 IP')->label();
            $grid->column('login_ip', '登陆 IP')->label();
            $grid->column('phone', '手机绑定')->display('点击查看')->modal(function (Modal $modal) {
                $modal->title('手机绑定');
                $modal->xl();

                return Phone::make();
            });
            $grid->column('wechat', '微信绑定')->display('点击查看')->modal(function (Modal $modal) {
                $modal->title('微信绑定');
                $modal->xl();

                return Wechat::make();
            });
            $grid->column('logined_at', '登陆时间');
            $grid->column('updated_at', '更新时间');
            $grid->column('created_at', '注册时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', '账户 ID');
                $filter->like('user.name', '用户昵称');
                $filter->in('register_type', '注册方式')->multipleSelect(AccountRegisterType::MAP);
                $filter->in('register_client_type', '注册客户端')->multipleSelect(ClientType::MAP);
                $group = function (Grid\Filter\Group $group) {
                    $group->equal('等于');
                    $group->gt('大于');
                    $group->lt('小于');
                    $group->nlt('大于等于');
                    $group->ngt('小于等于');
                };
                $filter->group('login_count', $group, '登陆次数');
                $filter->group('created_at', $group, '注册时间')->datetime();
                $filter->group('login_at', $group, '登陆时间')->date();
            });
        });
    }
}
