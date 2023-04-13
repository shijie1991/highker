<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Middleware;

use Closure;
use HighKer\Core\Exceptions\HighKerException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * 用户违规锁定中间件
 * Class ForbiddenUser.
 */
class LockedUser
{
    /**
     * @throws HighKerException
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->locked_at !== null && $request->user()->locked_at > now()) {
            $unLockedTime = Carbon::create($request->user()->locked_at);

            $diffForHumans = $unLockedTime->diffForHumans(now());

            throw new HighKerException('您的账户因违规已被锁定! 预计'.$diffForHumans.'解除锁定');
        }

        return $next($request);
    }
}
