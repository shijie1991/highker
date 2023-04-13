<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Jobs;

use Exception;
use HighKer\Core\Models\User;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Utils\NetUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadAvatar implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Model $user;
    protected string $avatar;

    public function __construct(User $user, string $avatar, $delay = false)
    {
        /* @var User user */
        $this->user = $user;

        $this->avatar = $avatar;

        // 设置延迟的时间，delay() 方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        // 下载头像
        $data = NetUtils::getFromUrl($this->avatar);
        $path = Highker::uploadDir('avatar').'/'.Str::random(40).'.jpg';
        Storage::put($path, $data);
        $this->user->avatar = $path;

        $this->user->save();

        return true;
    }
}
