<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support\Facades;

use HighKer\Core\Support\Wechat\MiniApp;
use Illuminate\Support\Facades\Facade;

class Wechat extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'easywechat.official_account';
    }

    public static function miniApp(): MiniApp
    {
        return app('miniApp');
    }
}
