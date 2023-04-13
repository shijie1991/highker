<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Jobs;

use EasyWeChat;
use HighKer\Core\Enum\AccountRegisterType;
use HighKer\Core\Enum\FeedStatus;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\AccountBase;
use HighKer\Core\Support\Facades\Wechat;
use HighKer\Core\Support\HighKer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AutoCheckFeed implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Model $feed;

    public function __construct(Model $feed, $delay = false)
    {
        $this->feed = $feed;

        // 设置延迟的时间，delay() 方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws EasyWeChat\Kernel\Exceptions\BadResponseException
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws HighKerException
     */
    public function handle()
    {
        // 动态审核 文字依靠站内 只检测图片
        $feed = $this->feed->loadMissing(['images']);

        // 如果图片不存在直接审核通过
        if ($feed->images->isEmpty()) {
            $feed->status = FeedStatus::APPROVE;
            $feed->save();

            return true;
        }

        $weChatAccount = AccountBase::getByAccountId($feed->user->account_id, AccountRegisterType::WECHAT);

        if (!$weChatAccount) {
            return false;
        }

        $app = Wechat::miniApp();

        foreach ($feed->images as $image) {
            $response = $app->checkMedia($weChatAccount->open_id, Storage::url($image['path']), 2);
            if ($response) {
                // 设置 审核任务为 动态 feed
                [$key, $expire] = Highker::getCacheKey('audit:type', 'task-type', [$response['trace_id']]);
                Redis::setex($key, $expire, 'feed');

                // 设置 trace_id 与 feed_id 关联
                [$key, $expire] = Highker::getCacheKey('audit:feed', 'task-id', [$response['trace_id']]);
                Redis::setex($key, $expire, $feed->id);

                // 设置 feed_id 关联列表
                [$key, $expire] = Highker::getCacheKey('audit:feed', 'task-list', [$feed->id]);
                Redis::sadd($key, $response['trace_id']);
                Redis::expire($key, $expire);

                // 设置 审核结果哈希集合
                [$key, $expire] = Highker::getCacheKey('audit:feed', 'audit-result', [$feed->id]);
                Redis::hset($key, $response['trace_id'], 0);
                Redis::expire($key, $expire);
            }
        }
    }
}
