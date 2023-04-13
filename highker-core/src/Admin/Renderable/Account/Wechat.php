<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Admin\Renderable\Account;

use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use HighKer\Core\Models\AccountWechat;

class Wechat extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(new AccountWechat(), function (Grid $grid) {
            $grid->model()->where('account_id', $this->key)->orderByDesc('id');
            $grid->column('id', 'ID');
            $grid->column('open_id', '小程序 ID');
            $grid->column('union_id', 'union_id');
            $grid->column('mp_open_id', '公众号 ID');
            $grid->column('logined_at', '登陆时间');
            $grid->column('created_at', '创建时间');
            $grid->column('updated_at', '更新时间');

            $grid->disableActions();
        });
    }
}
