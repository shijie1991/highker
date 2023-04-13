<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Jobs\AutoCheckFeed;
use HighKer\Core\Models\Feed;
use HighKer\Core\Requests\FeedCreateRequest;
use HighKer\Core\Requests\FeedUploadRequest;
use HighKer\Core\Requests\ReportRequest;
use HighKer\Core\Resources\CommonResource;
use HighKer\Core\Resources\FeedResource;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Utils\ImageUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FeedController extends BaseController
{
    protected array $type = ['new', 'hot', 'follow'];

    public function __construct()
    {
        $this->middleware(['forbidden_user'])->except(['index', 'show']);
        $this->middleware(['locked_user'])->only(['store']);
    }

    public function index(Request $request)
    {
        $type = $request->input('type', 'hot');

        $action = in_array($type, $this->type) ? $type : 'hot';
        $feeds = Feed::$action($request);

        // 添加分页参数
        $feeds->appends(['type' => $action]);

        if (Auth::check()) {
            // 是否关注
            Auth::user()->attachFollowStatus($feeds, fn ($feed) => $feed->user);
            // 是否点赞
            Auth::user()->attachLikeStatus($feeds);
        }

        return $this->success(FeedResource::collection($feeds));
    }

    public function upload(FeedUploadRequest $request)
    {
        try {
            // 获取 图片 尺寸
            [$width, $height] = ImageUtils::getSize($request->file('image'));
            $image['width'] = $width;
            $image['height'] = $height;
            $image['path'] = Storage::putFile(Highker::uploadDir('feed'), $request->file('image'));
        } catch (Throwable $e) {
            return $this->fail('上传失败');
        }

        return $this->success(CommonResource::make($image), '上传成功');
    }

    /**
     * 创建动态
     *
     * @throws HighKerException
     *
     * @return JsonResource|JsonResponse
     */
    public function store(FeedCreateRequest $request)
    {
        $feed = Feed::createFeed(
            Auth::user(),
            $request->input('content'),
            $request->input('images'),
            explode(',', $request->input('topic_id'))
        );

        // 队列定时审核
        dispatch(new AutoCheckFeed($feed, config('core.check_ttl.feed')));

        return $this->success(FeedResource::make($feed), '已提交 审核中');
    }

    /**
     * 查看动态
     *
     * @throws HighKerException
     * @throws AuthorizationException
     *
     * @return JsonResource|JsonResponse
     */
    public function show(Feed $feed)
    {
        $this->authorize('view', $feed);

        $feed->loadMissing(['content', 'images', 'user', 'topics']);

        Feed::feedView($feed);

        if (Auth::check()) {
            // 获取是否关注
            Auth::user()->attachFollowStatus($feed, fn ($feed) => $feed->user);
            // 是否点赞
            Auth::user()->attachLikeStatus($feed);
        }

        return $this->success(FeedResource::make($feed));
    }

    /**
     * @throws AuthorizationException
     *
     * @return JsonResource|JsonResponse|void
     */
    public function destroy(Feed $feed)
    {
        $this->authorize('delete', $feed);

        if (!$feed->delete()) {
            return $this->fail('系统错误 稍后再试');
        }

        return $this->success(null, '动态删除成功');
    }

    /**
     * 动态举报.
     *
     * @return JsonResource|JsonResponse
     */
    public function report(ReportRequest $request, Feed $feed)
    {
        Auth::user()->report($feed, $request->input('reason'));

        return $this->success(null, '举报成功');
    }

    public function likeList(Feed $feed)
    {
        $likeList = $feed->likers()->simplePaginate();

        return $this->success(FeedResource::collection($likeList));
    }

    public function like(Feed $feed)
    {
        Auth::user()->like($feed);

        return $this->success(null, '点赞成功');
    }

    public function unlike(Feed $feed)
    {
        Auth::user()->unlike($feed);

        return $this->success(null, '取消点赞成功');
    }
}
