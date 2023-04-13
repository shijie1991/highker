<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Console\Cron;

use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Support\HighKer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SetUserInactiveCommand extends Command
{
    protected $signature = 'corn:set-user-inactive';

    protected $description = '设置用户状态为不活跃';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws HighKerException
     */
    public function handle()
    {
        [$listKey] = Highker::getCacheKey('user:online', 'list');
        // 获取超过 5 分钟 未活动的
        $inactive = Redis::zrangebyscore($listKey, '-inf', now()->subMinutes(5)->timestamp);
        if ($inactive) {
            [$key] = Highker::getCacheKey('user:online', 'user');
            foreach ($inactive as $userId) {
                Redis::setbit($key, $userId, 0);
                Redis::zrem($listKey, $userId);
            }
            $this->line(count($inactive).'位用户不活跃');
        } else {
            $this->error('暂无用户');
        }
    }
}
