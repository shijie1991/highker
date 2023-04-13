<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;
use Illuminate\Support\Str;

// 只有 admin 的域名才注册 admin 路由
if (Str::startsWith(request()->getHost(), 'admin')) {
    Admin::routes();

    Route::group([
        'prefix'     => config('admin.route.prefix'),
        'namespace'  => config('admin.route.namespace'),
        'middleware' => config('admin.route.middleware'),
    ], function (Router $router) {

        $router->get('/', 'HomeController@index');

    });
}

