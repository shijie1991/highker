<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support;

use HighKer\Core\Exceptions\HighKerException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class HighKer
{
    /**
     * HighKer version.
     *
     * @var string
     */
    public const VERSION = '1.0.0';

    public static function db(): ConnectionInterface
    {
        return DB::connection(env('DB_CONNECTION', 'mysql'));
    }

    public static function getLongVersion(): string
    {
        return sprintf('highker-core <comment>version</comment> <info>%s</info>', self::VERSION);
    }

    public static function cache(): Repository
    {
        $cache = Cache::store(config('core.cache'));
        if (!$cache) {
            $cache = Cache::store();
        }

        return $cache;
    }

    /**
     * 获取缓存 key.
     *
     * @param $module
     * @param $needKey
     *
     * @throws HighKerException
     */
    public static function getCacheKey($module, $needKey, array $keyBind = [], int $defaultExpire = 3600): array
    {
        $config = implode('.', explode(':', $module));
        $keys = config('cache-keys.'.$config);
        if (!$keys) {
            throw new HighKerException('redis keys empty');
        }

        // 搜索 按 : 区分的 第一个词是否等于
        $cacheKey = Arr::where($keys, function ($value, $key) use ($needKey) {
            [$matchKey] = explode(':', $key);

            return $matchKey === $needKey;
        });

        if (!$cacheKey) {
            throw new HighKerException('cache-key empty');
        }

        // 只获取 一个
        $cacheKey = count($cacheKey) > 1 ? array_slice($cacheKey, 1) : $cacheKey;

        [$key, $expire] = Arr::divide($cacheKey);
        $key = head($key);
        $expire = head($expire) ? head($expire) : $defaultExpire;
        $key = $module.':'.Str::replaceArray('%s', $keyBind, preg_replace('/{[^}]+}/', '%s', $key));

        return [$key, $expire];
    }

    /**
     * 获取 redis 中的 所有 key.
     */
    public static function getRedisKeys(string $match = '*', int $count = 10): array
    {
        $it = null;
        $keysFound = [];
        do {
            [$it, $redisKeys] = Redis::scan($it, ['match' => $match, 'count' => $count]);

            if ($redisKeys) {
                $keysFound = array_merge($keysFound, $redisKeys);
            }
        } while ($it != 0);

        return $keysFound;
    }

    /**
     * 获取 websocket 链接.
     *
     * @throws HighKerException
     */
    public static function getChannel(): ?array
    {
        if (Auth::check()) {
            [$key, $expire] = Highker::getCacheKey('user:websocket', 'info', [auth()->id()]);
            if ($data = Redis::get($key)) {
                return json_decode($data, true);
            }
            $channel = app('saeChannel')->createChannel(Auth::user()->id);
            $data = [
                'channel' => $channel,
                'expire'  => now()->addSeconds($expire)->toDateTimeString(),
            ];
            Redis::setex($key, $expire, json_encode($data));

            return $data;
        }

        return null;
    }

    /**
     * @throws HighKerException
     */
    public static function uploadDir($dir)
    {
        return match ($dir) {
            'avatar' => 'user/avatar/'.now()->tz('PRC')->format('Y-m-d'),
            'faker'  => 'user/faker/'.now()->tz('PRC')->format('Y-m-d'),
            // 动态
            'feed' => 'feed/'.now()->tz('PRC')->format('Y-m-d'),
            // 评论图片
            'comment' => 'comment/'.now()->tz('PRC')->format('Y-m-d'),
            // 对话聊天 文件存储
            'chat' => 'chat/'.now()->tz('PRC')->format('Y-m-d'),

            default => throw new HighKerException('不支持当前目录'),
        };
    }
}
