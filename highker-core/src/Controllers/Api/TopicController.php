<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use HighKer\Core\Models\Topic;
use HighKer\Core\Models\TopicGroup;
use HighKer\Core\Resources\FeedResource;
use HighKer\Core\Resources\TopicResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicController extends BaseController
{
    protected array $type = ['new', 'hot'];

    public function __construct()
    {
        $this->middleware(['forbidden_user'])->except(['index', 'show']);
    }

    public function index()
    {
        // 如果用户登陆 获取我的关注分组
        $groups = TopicGroup::query()
            ->when(Auth::guest(), function (Builder $query) {
                return $query->where('id', '<>', 1);
            })
            ->with([
                'topics' => function (HasMany $query) {
                    $query->orderByDesc('feed_count')->orderByDesc('follow_count');
                },
            ])->orderBy('order')->get()->toArray();

        foreach ($groups as &$group) {
            // 如果用户登陆 获取 关注的话题
            if ($group['id'] === 1) {
                $group['topics'] = Auth::user()->subscriptions()->WithType(Topic::class)->latest()->with(['subscribable'])->get()->pluck('subscribable');
            }

            // 获取推荐话题
            if ($group['id'] === 2) {
                $group['topics'] = Topic::query()->orderByDesc('updated_at')->limit(15)->get();
            }
        }

        return $this->success(TopicResource::collection($groups));
    }

    public function feeds(Request $request, Topic $topic)
    {
        $type = $request->input('type', 'new');
        $action = in_array($type, $this->type) ? $type : 'new';

        $feeds = Topic::$action($request, $topic);

        // 添加分页参数
        $feeds->appends($request->input());

        if (Auth::check()) {
            // 是否关注
            Auth::user()->attachFollowStatus($feeds, fn ($feed) => $feed->user);
            // 是否点赞
            Auth::user()->attachLikeStatus($feeds);
        }

        return $this->success(FeedResource::collection($feeds));
    }

    public function show(Topic $topic)
    {
        if (Auth::check()) {
            // 是否关注
            Auth::user()->attachSubscriptionStatus($topic);
        }

        return $this->success(TopicResource::make($topic));
    }

    public function subscribe(Topic $topic)
    {
        Auth::user()->subscribe($topic);

        return $this->success(null, '关注成功');
    }

    public function unsubscribe(Topic $topic)
    {
        Auth::user()->unsubscribe($topic);

        return $this->success(null, '已取消关注');
    }
}
