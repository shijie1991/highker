<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Laravel\Telescope\Telescope;
use Studio\Totem\Totem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        Telescope::ignoreMigrations();
        Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Carbon::setLocale('zh');

        Totem::auth(function ($request) {
            $adminUser = Auth::guard('admin')->user();
            if (!$adminUser) {
                return false;
            }

            return $adminUser->id == 1;
        });
    }
}
