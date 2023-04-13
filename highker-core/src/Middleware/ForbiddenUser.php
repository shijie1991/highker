<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Middleware;

use Closure;
use HighKer\Core\Exceptions\HighKerException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 用户封禁中间件
 * Class ForbiddenUser.
 */
class ForbiddenUser
{
    /**
     * @throws HighKerException
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guest() && $request->user()->deleted_at !== null) {
            throw new HighKerException('您的账号已被封禁!');
        }

        return $next($request);
    }
}
