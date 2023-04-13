<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Models\Traits\Subscribable;
use HighKer\Core\Support\HighKer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class FeedTopic.
 *
 * @property int    id
 * @property string name
 * @property string description
 * @property string format_description
 * @property string cover
 * @property int    follow_count
 * @property int    feed_count
 * @property object top
 * @property string created_at
 * @property string updated_at
 */
class Topic extends BaseModel
{
    use Subscribable;

    protected $fillable = [];

    protected $hidden = ['pivot'];

    protected static function boot()
    {
        parent::boot();
    }

    public function formatDescription(): Attribute
    {
        return new Attribute(
            get: function () {
                Str::limit(str_replace(PHP_EOL, ' ', $this->description));
            }
        );
    }

    /**
     * 话题下 最新动态
     */
    public static function new(Request $request, Topic $topic)
    {
        return $topic->feeds()
            ->orderByDesc('created_at')
            ->with(['content', 'topics', 'user', 'images'])
            ->simplePaginate()
        ;

        // [$key, $expire] = Highker::getCacheKey('feed:topic', 'new', [$topic->id, $request->input('page', 1)]);
        //
        // return Cache::remember($key, $expire, function () use ($topic) {
        //     return $topic->feeds()
        //         ->orderByDesc('created_at')
        //         ->with(['content', 'topics', 'user', 'images'])
        //         ->simplePaginate()
        //     ;
        // });
    }

    /**
     * 话题下 热门动态
     *
     * @return LengthAwarePaginator
     */
    public static function hot(Request $request, Topic $topic)
    {
        // TODO 热门时间 !
        $date = now()->subDays(1000)->toDateString();

        return $topic->feeds()
            ->whereDate('feed.updated_at', '>=', $date)
            ->orderByDesc('feed.score')
            ->with(['content', 'topics', 'user', 'images'])
            ->simplePaginate()
        ;

        // [$key, $expire] = Highker::getCacheKey('feed:topic', 'hot', [$topic->id, $request->input('page', 1)]);
        //
        // return Cache::remember($key, $expire, function () use ($topic) {
        //     // 获取 30 天内 有过更新的动态
        //     $date = now()->subDays(1000)->toDateString();
        //
        //     return $topic->feeds()
        //         ->whereDate('feed.updated_at', '>=', $date)
        //         ->orderByDesc('feed.score')
        //         ->with(['content', 'topics', 'user', 'images'])
        //         ->simplePaginate()
        //     ;
        // });
    }

    public function feeds()
    {
        return $this->belongsToMany(Feed::class, FeedTopicRelation::class);
    }

    public function group()
    {
        return $this->belongsTo(TopicGroup::class);
    }
}
