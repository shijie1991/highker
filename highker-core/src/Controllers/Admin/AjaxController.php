<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use HighKer\Core\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class AjaxController
{
    /**
     * ajax 返回用户.
     *
     * @return Paginator
     */
    public function users(Request $request)
    {
        $search = $request->input('q');
        $result = User::query()
            ->where('name', 'like', '%' . $search . '%')
            ->simplePaginate()
        ;

        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        /* @var Paginator $result */
        $result->setCollection($result->getCollection()->map(function (User $user) {
            return ['id' => $user->id, 'text' => $user->name];
        }));

        return $result;
    }
}
