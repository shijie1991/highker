<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\ScoreLogType;
use HighKer\Core\Enum\UserPrivilege;
use HighKer\Core\Enum\UserTask;
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
 * @property int action_slug
 * @property int exp
 * @property int description
 * @property int created_at
 * @property int updated_at
 */
class TaskLog extends BaseModel
{
    protected $fillable = ['user_id', 'action_slug', 'exp', 'description'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($taskLog) {
            // 完成任务 用户有可能会升级
            $levelList = User::levelList($taskLog->user->level);

            // 下一个等级的信息
            $nextLevel = $levelList->where('unlocked', false)->first();

            // 如果 $nextLevel 不存在则说明 满级了 不增加经验
            if ($nextLevel) {
                if ($taskLog->user->exp + $taskLog->exp >= $nextLevel['exp']) {

                    // 完成任务增加经验 并升级
                    $taskLog->user->increment('exp', $taskLog->exp, ['level' => $nextLevel['level']]);

                    // 如果升级奖励 存在金币 则 增加金币
                    foreach ($nextLevel['award'] as $item) {
                        // 如果当前等级奖励的是金币 则 增加金币
                        if ($item['slug'] == UserPrivilege::SCORE) {
                            ScoreLog::createLog(
                                Auth::id(),
                                ScoreLogType::INCREMENT,
                                $item['quantity'],
                                '等级提升奖励'
                            );
                        }
                    }
                } else {
                    // 完成任务增加经验
                    $taskLog->user->increment('exp', $taskLog->exp);
                }
            }
        });
    }

    /**
     * @param mixed $userId
     *
     * @return Builder|Model
     */
    public static function createLog(int $userId, int $actionSlug, int $exp, string $description)
    {
        return TaskLog::query()->create([
            'user_id'     => $userId,
            'action_slug' => $actionSlug,
            'exp'         => $exp,
            'description' => $description,
        ]);
    }

    /**
     * @throws HighKerException
     */
    public static function dailyLogin()
    {
        // 每日登陆
        [$key, $expire] = Highker::getCacheKey('user:task', 'daily', [now()->toDateString(), Auth::id()]);

        if (!Redis::hexists($key, UserTask::DAILY_LOGIN)) {
            Redis::hset($key, UserTask::DAILY_LOGIN, 1);
            Redis::expire($key, $expire);

            // 设置连续登陆天数
            [$yesterdayKey] = Highker::getCacheKey('user:task', 'daily', [now()->subDays()->toDateString(), Auth::id()]);
            [$dailyKey, $expire] = Highker::getCacheKey('user:task', 'daily-login', [Auth::id()]);

            if (Redis::hexists($yesterdayKey, UserTask::DAILY_LOGIN)) {
                Redis::incr($dailyKey);
            } else {
                Redis::set($dailyKey, 1);
            }
            Redis::expire($dailyKey, $expire);

            // 记录 经验 Log
            TaskLog::createLog(
                Auth::id(),
                UserTask::DAILY_LOGIN,
                UserTask::MAP[UserTask::DAILY_LOGIN]['exp'],
                UserTask::MAP[UserTask::DAILY_LOGIN]['name']
            );

            // 记录 金币 Log
            ScoreLog::createLog(
                Auth::id(),
                ScoreLogType::INCREMENT,
                UserTask::MAP[UserTask::DAILY_LOGIN]['score'],
                UserTask::MAP[UserTask::DAILY_LOGIN]['name']
            );
        }
    }

    /**
     * @throws HighKerException
     */
    public static function dailyTask(int $task)
    {
        if (!in_array($task, UserTask::LIST)) {
            throw new HighKerException('undefined dailyTask');
        }

        if (Auth::check()) {
            [$key] = Highker::getCacheKey('user:task', 'daily', [now()->toDateString(), Auth::id()]);

            $taskCount = Redis::hget($key, $task);
            if ($taskCount < UserTask::MAP[$task]['must_count']) {
                Redis::hincrby($key, $task, 1);

                // 如果当前已经完成任务 则记录
                if ($taskCount == UserTask::MAP[$task]['must_count'] - 1) {
                    // 记录 Log
                    TaskLog::createLog(
                        Auth::id(),
                        $task,
                        UserTask::MAP[$task]['exp'],
                        UserTask::MAP[$task]['name']
                    );

                    // 记录 金币 Log
                    ScoreLog::createLog(
                        Auth::id(),
                        ScoreLogType::INCREMENT,
                        UserTask::MAP[$task]['score'],
                        UserTask::MAP[$task]['name']
                    );
                }
            }
        }
    }

    /**
     * @throws HighKerException
     */
    public static function onceTask(int $task)
    {
        if (!in_array($task, UserTask::LIST)) {
            throw new HighKerException('undefined onceTask');
        }

        if (Auth::check()) {
            // 是否已经完成该任务
            if (!TaskLog::query()->where('user_id', Auth::id())->where('action_slug', $task)->exists()) {
                // 记录 Log
                TaskLog::createLog(
                    Auth::id(),
                    $task,
                    UserTask::MAP[$task]['exp'],
                    UserTask::MAP[$task]['name']
                );

                // 记录 金币 Log
                ScoreLog::createLog(
                    Auth::id(),
                    ScoreLogType::INCREMENT,
                    UserTask::MAP[$task]['score'],
                    UserTask::MAP[$task]['name']
                );
            }
        }
    }

    /**
     * @throws HighKerException
     */
    public static function taskList()
    {
        $taskList = collect(UserTask::MAP);

        [$key] = Highker::getCacheKey('user:task', 'daily', [now()->toDateString(), Auth::id()]);
        [$dailyKey] = Highker::getCacheKey('user:task', 'daily-login', [Auth::id()]);

        return [
            'daily_login' => collect(Redis::get($dailyKey)),

            'daily_task' => $taskList->where('once', false)->map(function ($item) use ($key) {
                $completeCount = Redis::hget($key, $item['slug']);
                $item['complete_count'] = $completeCount ? (int) $completeCount : 0;
                $item['finish'] = $completeCount >= $item['must_count'];

                return $item;
            }),

            'once_task' => $taskList->where('once', true)->map(function ($item) {
                $item['finish'] = TaskLog::query()
                    ->where('user_id', Auth::id())
                    ->where('action_slug', $item['slug'])
                    ->exists()
                ;

                return $item;
            }),
        ];
    }

    public static function taskLog()
    {
        return TaskLog::query()
            ->where('user_id', Auth::id())->where('created_at', '>', now()->subDays(30))
            ->orderByDesc('id')
            ->simplePaginate()
        ;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
