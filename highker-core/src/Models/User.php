<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\ScoreExchange;
use HighKer\Core\Enum\UserGender;
use HighKer\Core\Enum\UserPrivilege;
use HighKer\Core\Enum\UserStatus;
use HighKer\Core\Enum\UserTask;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Traits\ChatMessageTraits;
use HighKer\Core\Models\Traits\FollowTraits;
use HighKer\Core\Models\Traits\HasDateTimeFormatter;
use HighKer\Core\Models\Traits\Liker;
use HighKer\Core\Models\Traits\Notifiable;
use HighKer\Core\Models\Traits\ReportTraits;
use HighKer\Core\Models\Traits\Subscriber;
use HighKer\Core\Observers\UserObserver;
use HighKer\Core\Support\HighKer;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Laravel\Sanctum\HasApiTokens;
use Throwable;

/**
 * Class User.
 *
 * @property int        id
 * @property int        account_id
 * @property int        status
 * @property int        is_vip
 * @property int        name
 * @property int        avatar
 * @property int        fake_name
 * @property int        fake_avatar
 * @property int        gender
 * @property int        score
 * @property int        level
 * @property int        exp
 * @property int        name_edited_at
 * @property int        vip_expired_at
 * @property int        locked_at
 * @property int        created_at
 * @property int        updated_at
 * @property UserFollow follows
 * @property UserInfo   info
 *
 * @method Subscription subscriptions()
 */
class User extends Authenticatable
{
    use Liker;
    use ChatMessageTraits;
    use FollowTraits;
    use Subscriber;
    use ReportTraits;
    use HasDateTimeFormatter;
    use HasApiTokens;
    use Notifiable;

    protected $table = 'user';

    protected $fillable = ['account_id', 'name', 'avatar', 'gender'];

    protected $casts = [
        'is_vip'         => 'boolean',
        'vip_expired_at' => 'datetime:Y-m-d',
    ];

    protected $hidden = [
        'account_id',
        'remember_token',
        'laravel_through_key',
        'pivot',
        'exp',
        'score',
        'fake_name',
        'fake_avatar',
        'vip_expired_at',
        'name_edited_at',
        'locked_at',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::observe(UserObserver::class);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // 设置分页时 默认每页的数量
        $this->setPerPage(config('core.page_size'));
    }

    /**
     * @throws HighKerException
     * @throws Throwable
     */
    public static function createUser(int $accountId, string $name, int $gender = UserGender::UNKNOWN, string $avatar = null): User
    {
        $account = Account::onWriteConnection()->find($accountId);
        if (!$account) {
            throw new HighKerException('账户不存在');
        }

        return Highker::db()->transaction(function () use ($accountId, $name, $gender, $avatar) {
            $user = User::query()->create([
                'name'       => $name,
                'account_id' => $accountId,
                'gender'     => $gender,
                'avatar'     => $avatar,
                'status'     => UserStatus::NORMAL,
            ]);

            UserInfo::query()->create(
                ['user_id' => $user->id]
            );

            return $user;
        });
    }

    public static function levelList($userLevel = 1)
    {
        // 等级对应奖励
        $levels = User::getLevelMap();

        // 完成所有任务 所需的经验
        $daySumExp = collect(UserTask::MAP)->where('once', false)->sum('exp');

        // 将天数 转换成 升级所需经验 (天数 * 当天所有经验)
        return $levels->map(function ($item, $key) use ($daySumExp, $userLevel) {
            return [
                'level'    => $key,
                'name'     => 'LV'.$key,
                'exp'      => $item['day'] * $daySumExp,
                'unlocked' => $userLevel >= $key,
                'award'    => $item['award'] ?? [],
            ];
        });
    }

    /**
     * @return array[]
     *
     * @throws HighKerException
     */
    public static function getPrerogativeCount($user)
    {
        // 已经解锁的所有特权奖励
        $awards = User::getLevelMap()->filter(function ($item, $key) use ($user) {
            return $key <= $user->level;
        })->pluck('award')->collapse();

        // 获取 已解锁的数量
        $addBoxCount = $awards->where('slug', UserPrivilege::ADD_BOX)->count();
        $getBoxCount = $awards->where('slug', UserPrivilege::GET_BOX)->count();
        $messageCount = $awards->where('slug', UserPrivilege::PRIVATE_MESSAGE)->count();

        [$addKey] = Highker::getCacheKey('user:box', 'add-count', [now()->toDateString(), $user->id]);
        [$getKey] = Highker::getCacheKey('user:box', 'get-count', [now()->toDateString(), $user->id]);
        [$messageKey] = Highker::getCacheKey('user:message', 'add-message-count', [now()->toDateString(), $user->id]);

        // 兑换的权益数量
        [$exchangeKey] = Highker::getCacheKey('user:exchange', 'info', [now()->toDateString(), $user->id]);

        $exchange = collect(Redis::hgetall($exchangeKey));

        $exchangeAddBoxCount = $exchange->has(ScoreExchange::ADD_BOX) ? $exchange[ScoreExchange::ADD_BOX] : 0;
        $exchangeGetBoxCount = $exchange->has(ScoreExchange::GET_BOX) ? $exchange[ScoreExchange::GET_BOX] : 0;
        $exchangeAddMessageCount = $exchange->has(ScoreExchange::PRIVATE_MESSAGE) ? $exchange[ScoreExchange::PRIVATE_MESSAGE] : 0;

        return [
            'used' => [
                'add_box_count' => Redis::get($addKey) ?? 0,
                'get_box_count' => Redis::get($getKey) ?? 0,
                'message_count' => Redis::get($messageKey) ?? 0,
            ],

            // 默认 3 次 VIP 增加 3 次
            'all' => [
                'add_box_count' => ($user->is_vip ? 6 : 3) + $addBoxCount + $exchangeAddBoxCount,
                'get_box_count' => ($user->is_vip ? 6 : 3) + $getBoxCount + $exchangeGetBoxCount,
                'message_count' => ($user->is_vip ? 6 : 3) + $messageCount + $exchangeAddMessageCount,
            ],
        ];
    }

    public static function getLevelMap()
    {
        // day 对应升级需要的 天数
        return collect([
            1 => [
                'day'   => 0,
                'award' => [
                    [
                        'slug'     => UserPrivilege::LEVEL_STYLE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::LEVEL_STYLE],
                        'quantity' => 0,
                    ],
                ],
            ],
            2 => [
                'day'   => 1,
                'award' => [
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 10,
                    ],
                ],
            ],
            3 => [
                'day'   => 2,
                'award' => [
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 15,
                    ],
                ],
            ],
            4 => [
                'day'   => 4,
                'award' => [
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 20,
                    ],
                ],
            ],
            5 => [
                'day'   => 6,
                'award' => [
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 25,
                    ],
                ],
            ],
            6 => [
                'day'   => 8,
                'award' => [
                    [
                        'slug'     => UserPrivilege::LEVEL_STYLE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::LEVEL_STYLE],
                        'quantity' => 0,
                    ],
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 30,
                    ],
                ],
            ],
            7 => [
                'day'   => 12,
                'award' => [
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 35,
                    ],
                ],
            ],
            8 => [
                'day'   => 16,
                'award' => [
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 40,
                    ],
                ],
            ],
            9 => [
                'day'   => 20,
                'award' => [
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 45,
                    ],
                ],
            ],
            10 => [
                'day'   => 24,
                'award' => [
                    [
                        'slug'     => UserPrivilege::COMMENT_STICKERS,
                        'name'     => UserPrivilege::MAP[UserPrivilege::COMMENT_STICKERS],
                        'quantity' => 0,
                    ],
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 50,
                    ],
                ],
            ],
            11 => [
                'day'   => 32,
                'award' => [
                    [
                        'slug'     => UserPrivilege::LEVEL_STYLE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::LEVEL_STYLE],
                        'quantity' => 0,
                    ],
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 55,
                    ],
                ],
            ],
            12 => [
                'day'   => 40,
                'award' => [
                    [
                        'slug'     => UserPrivilege::ADD_BOX,
                        'name'     => UserPrivilege::MAP[UserPrivilege::ADD_BOX],
                        'quantity' => 1,
                    ],
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 60,
                    ],
                ],
            ],
            13 => [
                'day'   => 48,
                'award' => [
                    [
                        'slug'     => UserPrivilege::GET_BOX,
                        'name'     => UserPrivilege::MAP[UserPrivilege::GET_BOX],
                        'quantity' => 1,
                    ],
                ],
            ],
            14 => [
                'day'   => 56,
                'award' => [
                    [
                        'slug'     => UserPrivilege::PRIVATE_MESSAGE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::PRIVATE_MESSAGE],
                        'quantity' => 1,
                    ],
                ],
            ],
            15 => [
                'day'   => 64,
                'award' => [
                    [
                        'slug'     => UserPrivilege::COMMENT_IMAGES,
                        'name'     => UserPrivilege::MAP[UserPrivilege::COMMENT_IMAGES],
                        'quantity' => 0,
                    ],
                ],
            ],
            16 => [
                'day'   => 74,
                'award' => [
                    [
                        'slug'     => UserPrivilege::LEVEL_STYLE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::LEVEL_STYLE],
                        'quantity' => 0,
                    ],
                ],
            ],
            17 => [
                'day'   => 84,
                'award' => [
                    [
                        'slug'     => UserPrivilege::SCORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::SCORE],
                        'quantity' => 70,
                    ],
                ],
            ],
            18 => [
                'day'   => 94,
                'award' => [
                    [
                        'slug'     => UserPrivilege::ADD_BOX,
                        'name'     => UserPrivilege::MAP[UserPrivilege::ADD_BOX],
                        'quantity' => 1,
                    ],
                ],
            ],
            19 => [
                'day'   => 104,
                'award' => [
                    [
                        'slug'     => UserPrivilege::GET_BOX,
                        'name'     => UserPrivilege::MAP[UserPrivilege::GET_BOX],
                        'quantity' => 1,
                    ],
                ],
            ],
            20 => [
                'day'   => 114,
                'award' => [
                    [
                        'slug'     => UserPrivilege::PRIVATE_MESSAGE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::PRIVATE_MESSAGE],
                        'quantity' => 1,
                    ],
                ],
            ],
            21 => [
                'day'   => 129,
                'award' => [
                    [
                        'slug'     => UserPrivilege::LEVEL_STYLE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::LEVEL_STYLE],
                        'quantity' => 0,
                    ],
                ],
            ],
            22 => [
                'day'   => 144,
                'award' => [
                    [
                        'slug'     => UserPrivilege::MORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::MORE],
                        'quantity' => 0,
                    ],
                ],
            ],
            23 => [
                'day'   => 159,
                'award' => [
                    [
                        'slug'     => UserPrivilege::MORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::MORE],
                        'quantity' => 0,
                    ],
                ],
            ],
            24 => [
                'day'   => 174,
                'award' => [
                    [
                        'slug'     => UserPrivilege::MORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::MORE],
                        'quantity' => 0,
                    ],
                ],
            ],
            25 => [
                'day'   => 189,
                'award' => [
                    [
                        'slug'     => UserPrivilege::MORE,
                        'name'     => UserPrivilege::MAP[UserPrivilege::MORE],
                        'quantity' => 0,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @throws HighKerException
     */
    public function visit(User $user)
    {
        if ($user->getKey() == $this->getKey()) {
            return true;
        }
        $where = [
            'user_id'    => $user->getKey(),
            'visitor_id' => $this->getKey(),
        ];

        $data = [
            'visit_count' => DB::raw('`visit_count`+1'),
        ];

        return tap(UserVisit::query()->firstOrNew($where), function ($instance) use ($data, $user) {
            // 十分钟 记录一次访客
            if ($instance->isDirty() || now() > $instance->updated_at->addMinutes(10)) {
                $instance->fill($data)->save();
                // 冗余数据
                $user->info->increment('visit_count');
                // 今日访问量
                [$key, $expire] = Highker::getCacheKey('user:visit', 'count', [now()->toDateString(), $user->getKey()]);
                Redis::incr($key);
                Redis::expire($key, $expire);
            }
        });
    }

    public function visits()
    {
        return $this->hasMany(UserVisit::class, 'user_id', 'id')->with(['visitor']);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->orderByDesc('created_at');
    }

    public function account(): HasOne
    {
        return $this->hasOne(Account::class);
    }

    public function info(): HasOne
    {
        return $this->hasOne(UserInfo::class);
    }

    public function feeds(): HasMany
    {
        return $this->hasMany(Feed::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
