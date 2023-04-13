<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Console\Cron;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use HighKer\Core\Enum\UserGender;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Jobs\saveFakeData;
use HighKer\Core\Support\HighKer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class JimuSpiderCommand extends Command
{
    protected $signature = 'corn:jimu-spider';

    protected $description = '积目爬虫';

    protected string $url = 'https://service.hitup.cn/api/v1/community/content/interest_circle_rec';

    protected array $query = [
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws GuzzleException
     * @throws HighKerException
     */
    public function handle()
    {
        $client = new Client();

        $response = $client->get($this->url, [
            'query'   => $this->query,
            'headers' => [
                'User-Agent'       => '%E7%A7%AF%E7%9B%AE/2 CFNetwork/1402.0.8 Darwin/22.2.0',
                'Client-TimeStamp' => now()->timestamp * 1000,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->info('status not 200');

            return;
        }

        $contents = json_decode($response->getBody()->getContents(), true);
        $data = collect($contents['data']['cards']);

        // 提取 正常的动态数据
        $data = $data->where('card_type', 2);

        // 和定时任务 配合一下 设置 delay 别一下子全部显示
        $interval = 60 * 60 / $data->count();
        $delay = 0;
        $dispatchCount = 0;

        foreach ($data as $value) {
            $feedInfo = $value['card_info']['feed_info'];

            // type 4 是图文 只获取图文动态
            $feedType = $feedInfo['type'];
            if ($feedType !== 4) {
                $this->info('$feedType 不是 4');

                continue;
            }

            $feedId = $feedInfo['feed_id'];
            // 如果已经 抓取过 则跳过
            [$key] = Highker::getCacheKey('fake:feed', 'list');
            if (Redis::sismember($key, $feedId)) {
                $this->info('sismember 存在'.$feedId);

                continue;
            }

            $user['id'] = $feedInfo['user']['id'];
            $user['avatar'] = $feedInfo['user']['avatar'];
            $user['name'] = $feedInfo['user']['nickname'];
            $user['gender'] = $feedInfo['user']['gender'] ?? UserGender::UNKNOWN;

            $feed['id'] = $feedId;
            $feed['content'] = $feedInfo['content']['text'];
            $feed['images'] = $feedInfo['content']['attachments'];
            $feed['location'] = $feedInfo['geoinfo']['city'] ?? null;

            ++$dispatchCount;
            $delay += $interval + rand(5, 300);
            dispatch(new saveFakeData($user, $feed, $delay));
        }

        $this->info('共提取 '.$data->count().' 条数据');
        $this->info('插入队列 '.$dispatchCount.' 条数据');
    }
}
