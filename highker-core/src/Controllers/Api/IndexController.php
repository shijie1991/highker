<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use HighKer\Core\Enum\UserRanking;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Article;
use HighKer\Core\Models\ArticleCategory;
use HighKer\Core\Resources\CommonResource;
use HighKer\Core\Resources\RankingResource;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Support\Ranking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class IndexController extends BaseController
{
    protected array $type = ['user', 'privacy', 'vip'];

    public function __construct()
    {
        $this->middleware(['forbidden_user'])->except(['index']);
    }

    /**
     * @throws HighKerException
     *
     * @return JsonResource|JsonResponse
     */
    public function index()
    {
        $data = [
            'cdn_url'    => config('core.url.cdn'),
            'static_url' => config('core.url.static'),
            'api_url'    => config('core.url.api'),
            'websocket'  => Highker::getChannel(),
        ];

        return $this->success(CommonResource::make($data));
    }

    /**
     * @throws HighKerException
     *
     * @return array|mixed
     */
    public function getSocket(Request $request)
    {
        $userId = $request->input('user_id', 1);

        [$key, $expire] = Highker::getCacheKey('user:websocket', 'info', [$userId]);
        if ($data = Redis::get($key)) {
            return json_decode($data, true);
        }
        $channel = app('saeChannel')->createChannel($userId);
        $data = [
            'channel' => $channel,
            'expire'  => now()->addSeconds($expire)->toDateTimeString(),
        ];
        Redis::setex($key, $expire, json_encode($data));

        return $this->success($data);
    }

    public function agreement(Request $request)
    {
        $type = $request->input('type', 'user');

        $type = in_array($type, $this->type) ? $type : 'user';

        $id = match ($type) {
            'user'    => 1,
            'privacy' => 2,
        };

        $article = Article::query()->findOrFail($id);

        return $this->success(CommonResource::make($article));
    }

    public function faq()
    {
        $categoryList = ArticleCategory::query()->where('parent_id', 3)->with(['articles'])->get();

        return $this->success(CommonResource::make($categoryList));
    }

    public function ranking()
    {
        return $this->success(CommonResource::make(UserRanking::MAP));
    }

    /**
     * @throws HighKerException
     */
    public function rankingInfo($slug)
    {
        if (!isset(UserRanking::MAP[$slug])) {
            $slug = UserRanking::USER;
        }

        $ranking = Ranking::getRanking($slug);

        if (Auth::check()) {
            // 是否关注
            Auth::user()->attachFollowStatus($ranking);
        }

        $data = [
            'ranking' => UserRanking::MAP[$slug],
            'list'    => $ranking,
        ];

        return $this->success(RankingResource::make($data));
    }
}
