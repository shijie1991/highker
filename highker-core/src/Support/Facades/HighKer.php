<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class HighKer.
 */
class HighKer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \HighKer\Core\Support\HighKer::class;
    }
}
