<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Console\Cron;

use HighKer\Core\Models\User;
use Illuminate\Console\Command;

class VipExpiredCommand extends Command
{
    protected $signature = 'corn:vip-expired';

    protected $description = '检测 VIP 过期用户并设置为非 VIP';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $today = now()->setTime(0, 0);

        $updateCount = User::query()
            ->whereDate('vip_expired_at', '<=', $today)
            ->update(['is_vip' => false, 'vip_expired_at' => null])
        ;

        $this->line('共 '.$updateCount.' VIP 到期');
    }
}
