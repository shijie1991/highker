<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

use Illuminate\Support\Facades\Route;

Route::domain(config('core.admin.route.domain'))
    ->middleware(config('core.admin.route.middleware'))
    ->group(__DIR__.'/../../routes/admin.php')
;
