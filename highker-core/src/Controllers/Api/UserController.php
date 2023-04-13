<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use HighKer\Core\Enum\ScoreExchange;
use HighKer\Core\Enum\UserReviewType;
use HighKer\Core\Enum\UserTask;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Administrator;
use HighKer\Core\Models\Feed;
use HighKer\Core\Models\ScoreLog;
use HighKer\Core\Models\TaskLog;
use HighKer\Core\Models\User;
use HighKer\Core\Models\UserInfoReview;
use HighKer\Core\Notifications\UserInfoResetNotifications;
use HighKer\Core\Requests\ScoreExchangeRequest;
use HighKer\Core\Requests\UserSettingRequest;
use HighKer\Core\Resources\CommonResource;
use HighKer\Core\Resources\FeedResource;
use HighKer\Core\Resources\ScoreLogResource;
use HighKer\Core\Resources\UserFollowResource;
use HighKer\Core\Resources\UserResource;
use HighKer\Core\Resources\UserVisitsCollectionResource;
use HighKer\Core\Support\HighKer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends BaseController
{
    public function index(User $user)
    {
        $user = $user->loadMissing('info');

        if (Auth::check()) {
            Auth::user()->visit($user);

            // 是否关注
            Auth::user()->attachFollowStatus($user);
        }

        return $this->success(UserResource::make($user));
    }

    public function feeds(User $user)
    {
        $feeds = Feed::getFeedsByUserId($user);

        if (Auth::check()) {
            // 是否关注
            Auth::user()->attachFollowStatus($feeds, fn ($feed) => $feed->user);
            // 是否点赞
            Auth::user()->attachLikeStatus($feeds);
        }

        return $this->success(FeedResource::collection($feeds));
    }

    /**
     * @throws HighKerException
     */
    public function level()
    {
        $user = Auth::user();

        $levelList = User::levelList($user->level);

        $result = [
            'level_info' => [
                'level'    => $user->level,
                'exp'      => $user->exp,
                'next_exp' => $levelList->where('level', '>', $user->level)->pluck('exp')->first(),
            ],
            'prerogative' => User::getPrerogativeCount($user),
            'level_list'  => $levelList,
        ];

        return $this->success(CommonResource::make($result));
    }

    /**
     * @throws HighKerException
     */
    public function task()
    {
        $taskList = TaskLog::taskList();

        return $this->success(CommonResource::collection($taskList));
    }

    public function taskLog()
    {
        $taskList = TaskLog::taskLog();

        return $this->success(CommonResource::collection($taskList));
    }

    /**
     * @return JsonResource|JsonResponse|void
     *
     * @throws AuthorizationException
     */
    public function follow(User $user)
    {
        $this->authorize('follow', $user);

        if (Auth::user()->isFollowing($user)) {
            return $this->fail('已关注该用户');
        }

        Auth::user()->follow($user);

        return $this->success(null, '关注成功');
    }

    public function unfollow(User $user)
    {
        if (!Auth::user()->isFollowing($user)) {
            return $this->fail('未关注该用户');
        }

        Auth::user()->unfollow($user);

        return $this->success(null, '取消关注成功');
    }

    public function following(User $user)
    {
        $followings = $user->followings()->orderByDesc('pivot_created_at')->simplePaginate();

        if (Auth::check()) {
            // 是否关注
            Auth::user()->attachFollowStatus($followings);
        }

        return $this->success(UserFollowResource::collection($followings));
    }

    public function followers(User $user)
    {
        $followers = $user->followers()->orderByDesc('pivot_created_at')->simplePaginate();

        if (Auth::check()) {
            // 是否关注
            Auth::user()->attachFollowStatus($followers);
        }

        return $this->success(UserFollowResource::collection($followers));
    }

    /**
     * @return JsonResource|JsonResponse
     */
    public function visits()
    {
        $user = Auth::user();

        $visits = collect();
        if ($user->is_vip) {
            $visits = Auth::user()->visits()->orderByDesc('updated_at')->simplePaginate();
            Auth::user()->attachFollowStatus($visits, fn ($visit) => $visit->visitor);
        }

        return $this->success(UserVisitsCollectionResource::make($visits));
    }

    /**
     * @return JsonResource|JsonResponse
     */
    public function score()
    {
        $data = [
            'score' => Auth::user()->score,
            'list'  => ScoreExchange::MAP,
        ];

        return $this->success(UserVisitsCollectionResource::make($data));
    }

    public function scoreLog()
    {
        $scoreList = ScoreLog::scoreLog();

        return $this->success(ScoreLogResource::make($scoreList));
    }

    /**
     * @throws HighKerException
     */
    public function scoreExchange(ScoreExchangeRequest $request)
    {
        $privilege = $request->input('privilege');

        if (Auth::user()->score < ScoreExchange::MAP[$privilege]['score']) {
            $this->fail('金币不足 无法兑换');
        }

        ScoreLog::exchange(Auth::id(), $privilege);

        return $this->success(null, '兑换成功');
    }

    /**
     * @throws HighKerException
     */
    public function setting(UserSettingRequest $request)
    {
        $userData = $request->only(['name', 'avatar']);
        $userInfoData = $request->only(['emotion', 'purpose', 'region', 'birthday', 'description']);

        // 头像上传
        if ($request->hasFile('avatar')) {
            if ($result = Storage::putFile(Highker::uploadDir('avatar'), $request->file('avatar'))) {
                $userData['avatar'] = $result;
            }
        }

        // 昵称审核
        if (isset($userData['name'])) {
            $sensitiveKeywordFilter = app('sensitiveKeywordFilter');
            if ($sensitiveKeywordFilter->isLegal($userData['name'])) {
                UserInfoReview::createReview(Auth::id(), UserReviewType::NAME, $userData['name']);
            } else {
                unset($userData['name']);
                Auth::user()->notify(new UserInfoResetNotifications(Administrator::query()->find(1), '您设置的昵称因违反社区规定，已被重置。'));
            }
        }

        // 个新签名审核
        if (isset($userInfoData['description'])) {
            $sensitiveKeywordFilter = app('sensitiveKeywordFilter');
            if ($sensitiveKeywordFilter->isLegal($userInfoData['description'])) {
                UserInfoReview::createReview(Auth::id(), UserReviewType::DESCRIPTION, $userInfoData['description']);
            } else {
                unset($userInfoData['description']);
                Auth::user()->notify(new UserInfoResetNotifications(Administrator::query()->find(1), '您设置的个性签名因违反社区规定，已被重置。'));
            }
        }

        // TODO 头像还未作处理

        Auth::user()->fill($userData)->save();

        Auth::user()->info->fill($userInfoData)->save();

        if (!TaskLog::query()->where('user_id', Auth::id())->where('action_slug', UserTask::COMPLETE_USER_INFO)->exists()) {
            if (Auth::user()->info->emotion && Auth::user()->info->purpose && Auth::user()->info->region && Auth::user()->info->birthday && Auth::user()->info->description) {
                // 新手任务 完善用户信息
                TaskLog::onceTask(UserTask::COMPLETE_USER_INFO);
            }
        }

        return $this->success(null, '保存成功');
    }
}
