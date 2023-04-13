<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Middleware;

use Closure;
use HighKer\Core\Enum\UserGender;
use HighKer\Core\Exceptions\HighKerException;
use Illuminate\Http\Request;

/**
 * 检测用户是否设置性别.
 *
 * Class CheckGender
 */
class CheckGender
{
    /**
     * @throws HighKerException
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->gender == UserGender::UNKNOWN) {
            throw new HighKerException('请先设置性别');
        }

        return $next($request);
    }
}
