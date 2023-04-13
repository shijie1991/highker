<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

use Illuminate\Support\Facades\Route;

Route::domain(config('core.api.route.domain'))
    ->middleware(config('core.api.route.middleware'))
    ->group(__DIR__.'/../../routes/api.php')
;
