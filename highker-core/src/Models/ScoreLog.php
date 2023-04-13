<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\ScoreExchange;
use HighKer\Core\Enum\ScoreLogType;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Support\HighKer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

/**
 * @property int id
 * @property int user_id
 * @property int type
 * @property int score
 * @property int description
 * @property int created_at
 * @property int updated_at
 */
class ScoreLog extends BaseModel
{
    protected $fillable = ['user_id', 'type', 'score', 'description'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($scoreLog) {
            if ($scoreLog->type == ScoreLogType::INCREMENT) {
                // 完成任务 增加用户积分
                $scoreLog->user->increment('score', $scoreLog->score);
            }

            if ($scoreLog->type == ScoreLogType::DECREMENT) {
                // 兑换奖励 减少用户积分
                $scoreLog->user->decrement('score', $scoreLog->score);
            }
        });
    }

    /**
     * @param mixed $userId
     *
     * @return Builder|Model
     */
    public static function createLog(int $userId, int $type, int $score, string $description)
    {
        return ScoreLog::query()->create([
            'user_id'     => $userId,
            'type'        => $type,
            'score'       => $score,
            'description' => $description,
        ]);
    }

    /**
     * @param $userId
     * @param $privilege
     *
     * @throws HighKerException
     */
    public static function exchange($userId, $privilege)
    {
        ScoreLog::createLog(
            $userId,
            ScoreLogType::DECREMENT,
            ScoreExchange::MAP[$privilege]['score'],
            '兑换'.ScoreExchange::MAP[$privilege]['name']
        );

        // redis 记录兑换权益及数量
        [$key, $expire] = Highker::getCacheKey('user:exchange', 'info', [now()->toDateString(), Auth::id()]);
        Redis::hincrby($key, $privilege, ScoreExchange::MAP[$privilege]['count']);
        Redis::expire($key, $expire);
    }

    public static function scoreLog()
    {
        return ScoreLog::query()
            ->where('user_id', Auth::id())
            ->where('created_at', '>', now()->subDays(30))
            ->orderByDesc('id')
            ->simplePaginate()
        ;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
