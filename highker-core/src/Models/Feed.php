<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\CommentLevel;
use HighKer\Core\Enum\FeedStatus;
use HighKer\Core\Enum\UserTask;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Traits\Likeable;
use HighKer\Core\Observers\FeedObserver;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Utils\IpUtils;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class Feed.
 *
 * @property int         id
 * @property int         user_id
 * @property string      status
 * @property int         is_top
 * @property int         location
 * @property FeedContent content
 * @property int         view_count
 * @property int         like_count
 * @property int         comment_count
 * @property string      deleted_at
 * @property string      created_at
 * @property string      updated_at
 * @property string      friendly_date
 * @property string      format_content
 * @property FeedImage   images
 * @property User        user
 * @property Topic       topics
 * @property User        followings
 */
class Feed extends BaseModel
{
    use SoftDeletes;
    use Likeable;

    protected $fillable = ['user_id'];

    protected $hidden = ['pivot'];

    /**
     * 属性默认值
     *
     * @var array
     */
    protected $attributes = [
        'status'        => FeedStatus::PENDING,
        'view_count'    => 0,
        'like_count'    => 0,
        'comment_count' => 0,
    ];

    protected static function boot()
    {
        parent::boot();

        // 匿名全局作用域
        static::addGlobalScope('approve', function (Builder $builder) {
            $builder->where('status', FeedStatus::APPROVE);
        });

        static::observe(FeedObserver::class);
    }

    public function formatContent(): Attribute
    {
        return new Attribute(
            get: function () {
                if (empty($this->content->text)) {
                    return '';
                }

                return Str::limit(str_replace(PHP_EOL, '', $this->content->text), 40);
            }
        );
    }

    /**
     * @param      $user
     * @param      $text
     * @param      $images
     * @param null $topicIds
     *
     * @throws HighKerException
     */
    public static function createFeed($user, $text, $images, $topicIds = null)
    {
        try {
            return Highker::db()->transaction(function () use ($user, $text, $images, $topicIds) {
                $feed = Feed::query()->create([
                    'user_id' => $user->id,
                ]);

                if ($topicIds) {
                    // 取出话题ID (避免数据错误)
                    $topicIds = Topic::query()->whereIn('id', $topicIds)->get(['id']);

                    // 动态 话题 关联
                    $feed->topics()->sync($topicIds);
                }

                if ($text) {
                    $feed->content()->create(['text' => $text]);
                }

                if ($images) {
                    // 追加 user_id
                    foreach ($images as $image) {
                        $image['user_id'] = $feed->user_id;
                    }

                    $feed->images()->createMany($images);
                }

                return $feed->load([
                    'content',
                    'topics',
                    'images' => function (HasMany $query) {
                        $query->withoutGlobalScope('approve');
                    },
                ]);
            });
        } catch (Throwable $e) {
            throw new HighKerException($e->getMessage());
        }
    }

    /**
     * 热门动态
     */
    public static function hot(Request $request)
    {
        // 获取 30 天内 有过更新的动态
        $date = now()->subDays(30)->toDateString();

        return Feed::query()
            ->whereDate('updated_at', '>=', $date)
            ->orderByDesc('score')
            ->orderByDesc('id')
            ->with(['content', 'topics', 'user', 'images'])
            ->paginate()
        ;

        // [$key, $expire] = Highker::getCacheKey('feed:list', 'hot', [$request->input('page', 1)]);
        //
        // return Cache::remember($key, $expire, function () {
        //     // 获取 30 天内 有过更新的动态
        //     $date = now()->subDays(50)->toDateString();
        //
        //     return Feed::query()
        //         ->whereDate('updated_at', '>=', $date)
        //         ->orderByDesc('score')
        //         ->with(['content', 'topics', 'user', 'images'])
        //         ->simplePaginate()
        //     ;
        // });
    }

    /**
     * 最新动态
     */
    public static function new(Request $request)
    {
        return Feed::query()
            ->orderByDesc('id')
            ->with(['content', 'topics', 'user', 'images'])
            ->paginate()
        ;

        // [$key, $expire] = Highker::getCacheKey('feed:list', 'new', [$request->input('page', 1)]);
        //
        // return Cache::remember($key, $expire, function () {
        //     return Feed::query()
        //         ->orderByDesc('created_at')
        //         ->with(['content', 'topics', 'user', 'images'])
        //         ->simplePaginate()
        //     ;
        // });
    }

    /**
     * 我关注的人动态
     *
     * @throws AuthenticationException
     * @throws HighKerException
     */
    public static function follow(Request $request)
    {
        if (Auth::guest()) {
            throw new AuthenticationException();
        }

        return Feed::query()
            ->whereIn('user_id', Auth::user()->followings->pluck('id'))
            ->orderByDesc('id')
            ->with(['content', 'topics', 'user', 'images'])
            ->simplePaginate()
        ;

        // [$key, $expire] = Highker::getCacheKey('feed:list', 'follow', [Auth::id(), $request->input('page', 1)]);
        //
        // return Cache::remember($key, $expire, function () {
        //     return Feed::query()
        //         ->whereIn('user_id', Auth::user()->followings->pluck('id'))
        //         ->orderByDesc('created_at')
        //         ->with(['content', 'topics', 'user', 'images'])
        //         ->simplePaginate()
        //     ;
        // });
    }

    public static function getFeedsByUserId(User $user)
    {
        return Feed::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with(['content', 'topics', 'user', 'images'])
            ->simplePaginate()
        ;
    }

    /**
     * @throws HighKerException
     */
    public static function feedView(Feed $feed)
    {
        // 查看动态详情 记录并增加分数.
        [$key, $expire] = Highker::getCacheKey('feed:view', 'ip-view', [$feed->id, IpUtils::getIp()]);
        if (!Redis::get($key)) {
            $score = $feed->created_at > now()->subDays(7) ? 2 : 1;
            $feed->increment('score', $score);
            $feed->increment('view_count');
            Redis::setex($key, $expire, 1);
        }

        // 记录用户 浏览动态
        if (Auth::check()) {
            [$key, $expire] = Highker::getCacheKey('feed:view', 'user-view', [$feed->id, Auth::id()]);
            if (!Redis::get($key)) {
                Redis::setex($key, $expire, 1);

                // 浏览帖子任务
                TaskLog::dailyTask(UserTask::VIEW_FEED);
            }
        }
    }

    public function content(): hasOne
    {
        return $this->hasOne(FeedContent::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(FeedImage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, FeedTopicRelation::class)->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('level', CommentLevel::COMMENT);
    }
}
