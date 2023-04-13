<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Console\Cron;

use HighKer\Core\Jobs\GenerateFakeAvatar;
use HighKer\Core\Models\User;
use Illuminate\Console\Command;

class CheckFakeAvatarCommand extends Command
{
    protected $signature = 'corn:check-avatar';

    protected $description = '检查站内用户虚拟头像';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $users = User::query()->whereNull('fake_avatar')->limit(10)->get();

        if ($users->isEmpty()) {
            $this->info('未获取到 虚拟头像为空的用户');

            return;
        }

        foreach ($users as $user) {
            dispatch(new GenerateFakeAvatar($user, 5));
        }
        $this->info('获取到 '.$users->count().' 个未设置头像的用户');
    }
}
