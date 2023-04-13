<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Admin\Renderable\Account;

use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Table;
use HighKer\Core\Models\AccountPhone;

class Phone extends LazyRenderable
{
    public function render()
    {
        // 获取ID
        $id = $this->key;

        $data = AccountPhone::query()->where('account_id', $id)->get([
            'id',
            'phone',
            'is_active',
            'login_count',
            'logined_at',
            'created_at',
            'updated_at',
        ])->toArray();

        $rows = collect($data)->map(function ($item) {
            $item['is_active'] = $item['is_active'] ? '绑定中' : '已解绑';

            return $item;
        })->toArray();

        $headers = [
            'ID',
            '手机号',
            '是否绑定',
            '登陆次数',
            '登陆时间',
            '创建时间',
            '更新时间',
        ];

        return Table::make($headers, $rows);
    }
}
