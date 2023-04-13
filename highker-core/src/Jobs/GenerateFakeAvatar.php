<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Jobs;

use HighKer\Core\Enum\UserGender;
use HighKer\Core\Models\User;
use HighKer\Core\Support\Avatar;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Utils\NetUtils;
use HighKer\Core\Utils\StringUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GenerateFakeAvatar implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected User $user;

    /**
     * 任务可尝试次数.
     */
    public int $tries = 3;

    public function __construct(User $user, $delay = false)
    {
        /* @var User user */
        $this->user = $user;

        // 设置延迟的时间，delay() 方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    public function handle()
    {
        if ($this->user->fake_avatar) {
            return true;
        }

        $avatar = new Avatar();
        $this->user->gender === UserGender::MALE ? $avatar->male() : $avatar->female();
        $url = $avatar->avatar($this->user->id);

        try {
            // 下载头像
            $data = NetUtils::getFromUrl($url, 10);
            $path = Highker::uploadDir('faker').'/'.Str::random(40).'.svg';
            Storage::put($path, $data);
            $this->user->fake_avatar = $path;
        } catch (Throwable $e) {
            Log::info($e->getMessage());

            // 下载失败 从数据库里随机获取一个
            $user = User::query()->where('gender', $this->user->gender)
                ->whereNotNull('fake_avatar')
                ->inRandomOrder()
                ->first()
            ;

            $this->user->fake_avatar = $user->fake_avatar;
        }

        // 同时生成 虚拟昵称
        $this->user->fake_name = StringUtils::generateNickname();

        $this->user->save();

        return true;
    }
}
