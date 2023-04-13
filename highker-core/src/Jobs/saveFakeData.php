<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Jobs;

use HighKer\Core\Enum\AccountRegisterType;
use HighKer\Core\Enum\ClientType;
use HighKer\Core\Enum\FeedStatus;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Account;
use HighKer\Core\Models\AccountBase;
use HighKer\Core\Models\Feed;
use HighKer\Core\Models\User;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Utils\ImageUtils;
use HighKer\Core\Utils\NetUtils;
use HighKer\Core\Utils\StringUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class saveFakeData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $user;
    protected array $feed;

    public function __construct(array $user, $feed, $delay = false)
    {
        $this->user = $user;

        $this->feed = $feed;

        // 指定执行的队列
        $this->onQueue('low');

        // 设置延迟的时间，delay() 方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    /**
     * @throws Throwable
     * @throws HighKerException
     */
    public function handle()
    {
        $fakeUser = null;

        [$key] = Highker::getCacheKey('fake:user', 'list');
        // 如果保存过 该用户
        if (Redis::getbit($key, $this->user['id'])) {
            /** @var Model $fakeModel */
            $fakeModel = AccountBase::getModel(AccountRegisterType::FAKER);
            $fakeAccount = $fakeModel::query()->where('open_id', $this->user['id'])->first();
            if ($fakeAccount) {
                $fakeUser = User::query()->where('account_id', $fakeAccount->account_id)->first();
            }
        } else {
            // // 下载用户头像
            $imageData = NetUtils::getFromUrl($this->user['avatar'], 30);
            $ext = ImageUtils::getExtensionFromBinary($imageData);
            $path = Highker::uploadDir('avatar').'/'.Str::random(40).'.'.$ext;

            // 如果用户头像下载不成功 跳出
            if (!Storage::put($path, $imageData)) {
                throw new HighKerException('用户头像下载失败!');
            }

            // 替换 字符串 将积目替换为嗨刻
            $name = str_replace('积目', '嗨刻', $this->user['name']);

            $fakeUser = Highker::db()->transaction(function () use ($path, $name) {
                // 创建虚拟账号
                $account = Account::createAccount('12345678', '127.0.0.1', AccountRegisterType::FAKER, ClientType::PC);
                // 创建虚拟开放平台
                AccountBase::createOpen($account->id, $this->user['id'], AccountRegisterType::FAKER, $this->user);
                // 创建用户
                return User::createUser($account->id, $name, $this->user['gender'], $path);
            });

            // 记录 用户
            Redis::setbit($key, $this->user['id'], 1);
        }

        if (!$fakeUser) {
            throw new HighKerException('未找到 fakeUser!');
        }

        $images = [];
        if ($this->feed['images']) {
            foreach ($this->feed['images'] as $key => $image) {
                // 如果图片尺寸太大 就不下载了 容易出错
                if ($image['data']['w'] < 4000 && $image['data']['h'] < 4000) {
                    // 下载动态图片
                    $imageData = NetUtils::getFromUrl($image['data']['url'], 30);
                    $ext = ImageUtils::getExtensionFromBinary($imageData);
                    $path = Highker::uploadDir('feed').'/'.Str::random(40).'.'.$ext;
                    if (Storage::put($path, $imageData)) {
                        $images[$key]['path'] = $path;
                        $images[$key]['width'] = $image['data']['w'];
                        $images[$key]['height'] = $image['data']['h'];
                    }
                }
            }
        }

        // 记录 动态 ID
        [$key] = Highker::getCacheKey('fake:feed', 'list');
        Redis::sadd($key, $this->feed['id']);

        if (!$this->feed['content'] && !$images) {
            throw new HighKerException('内容和图片都不存在');
        }

        // 替换 字符串 将积目替换为嗨刻
        $content = str_replace('积目', '嗨刻', $this->feed['content']);

        $fakeFeed = Feed::createFeed($fakeUser, $content, $images);
        $fakeFeed->status = FeedStatus::APPROVE;
        $fakeFeed->location = $this->feed['location'];
        $fakeFeed->save();
    }
}
