<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Middleware;

use Closure;
use HighKer\Core\Enum\NoticeEvent;
use HighKer\Core\Enum\NoticeType;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Administrator;
use HighKer\Core\Models\TaskLog;
use HighKer\Core\Notifications\UserSystemNotifications;
use HighKer\Core\Support\HighKer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

/**
 * 记录用户在线
 * Class OnlineUser.
 */
class OnlineUser
{
    /**
     * @throws HighKerException
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $this->online();

            $this->fetchUnReadNotices();

            // 每日登陆
            TaskLog::dailyLogin();
        }

        return $next($request);
    }

    /**
     * @throws HighKerException
     */
    private function online()
    {
        // 设置用户在线
        [$key] = Highker::getCacheKey('user:online', 'user');
        if (!Redis::getbit($key, Auth::user()->id)) {
            Redis::setbit($key, Auth::user()->id, 1);
        }

        // 设置在线用户列表
        [$key] = Highker::getCacheKey('user:online', 'list');
        Redis::zadd($key, [Auth::user()->id => now()->timestamp]);
    }

    /**
     * @throws HighKerException
     */
    private function fetchUnReadNotices()
    {
        [$key, $expire] = Highker::getCacheKey('other:notices', 'fetch-system-notice', [Auth::user()->id]);

        // 每十分钟 获取一次 比较合理
        if (!Redis::get($key)) {
            // 获取已阅读的 系统通知 最后记录时间
            $lastNotice = Auth::user()->notifications()
                ->where('notice_type', NoticeType::SYSTEM)
                ->where('event', NoticeEvent::SYSTEM_NOTICE)
                ->whereNotNull('last_at')
                ->first()
            ;

            // 获取 大于最近 15 天的 未读 系统通知
            $unReadNotices = DatabaseNotification::query()
                ->where('notifiable_type', Administrator::class)
                ->where('notice_type', NoticeType::SYSTEM)
                ->where('event', NoticeEvent::SYSTEM_NOTICE)
                ->whereDate('created_at', '>=', now()->subDays(15)->toDateString())
                ->when(!is_null($lastNotice), function (Builder $query) use ($lastNotice) {
                    return $query->whereDate('created_at', '>', $lastNotice->last_at);
                })
                ->get()
            ;

            // 发送通知
            $unReadNotices->map(function ($notice) {
                Auth::user()->notify(new UserSystemNotifications($notice));
            });

            Redis::setex($key, $expire, true);
        }
    }
}
