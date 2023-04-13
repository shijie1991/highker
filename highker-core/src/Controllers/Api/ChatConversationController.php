<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use HighKer\Core\Enum\NoticeType;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\ChatConversation;
use HighKer\Core\Models\ChatMessage;
use HighKer\Core\Models\User;
use HighKer\Core\Requests\ChatMessageCreateRequest;
use HighKer\Core\Resources\ChatConversationCollectionResource;
use HighKer\Core\Resources\ChatConversationResource;
use HighKer\Core\Support\Facades\Chat;
use HighKer\Core\Support\HighKer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Throwable;

class ChatConversationController extends BaseController
{
    protected array $type = ['box', 'private'];

    public function __construct()
    {
        $this->middleware(['forbidden_user']);
        $this->middleware(['locked_user'])->only(['store']);
    }

    /**
     * @return JsonResource|JsonResponse
     */
    public function index(Request $request, Application $app)
    {
        $type = $request->input('type', 'box');
        $action = in_array($type, $this->type) ? $type : 'box';

        $conversations = $app->call([$this, $action]);

        // 添加分页参数
        $conversations->appends(['type' => $action]);

        return $this->success(ChatConversationCollectionResource::make($conversations));
    }

    /**
     * 盲盒.
     */
    public function box(): Paginator
    {
        return Chat::conversations()->from(Auth::user())->isPrivate(false)->get();
    }

    /**
     * 私信
     */
    public function private(): Paginator
    {
        return Chat::conversations()->from(Auth::user())->isPrivate()->get();
    }

    public function exist(User $user)
    {
        // 检测 双方是否 私信过
        $exist = Chat::conversations()->conversationsBetweenUser(Auth::user(), $user);

        $result = [
            'conversation_id' => optional($exist)->id,
        ];

        return $this->success(ChatConversationResource::make($result));
    }

    /**
     * 获取 未读 小红点.
     *
     * @return JsonResource|JsonResponse
     */
    public function red(Request $request)
    {
        $data = [
            'private_count'     => Chat::conversations()->from(Auth::user())->isPrivate()->unreadCount(),
            'box_count'         => Chat::conversations()->from(Auth::user())->isPrivate(false)->unreadCount(),
            'system_count'      => Auth::user()->notifications()->where('notice_type', NoticeType::SYSTEM)->unread()->count(),
            'interactive_count' => Auth::user()->notifications()->where('notice_type', NoticeType::INTERACTIVE)->unread()->count(),
        ];

        return $this->success(ChatConversationResource::make($data));
    }

    /**
     * 创建私聊.
     *
     * @throws AuthorizationException
     * @throws HighKerException
     *
     * @return JsonResource|JsonResponse|void
     */
    public function store(ChatMessageCreateRequest $request, User $user)
    {
        $this->authorize('sendMessage', $user);

        $prerogative = User::getPrerogativeCount(Auth::user());

        if ($prerogative['used']['message_count'] >= $prerogative['all']['message_count']) {
            return $this->fail('私信次数不足');
        }

        $db = HighKer::db();
        $db->beginTransaction();

        try {
            // 创建私聊对话
            $conversation = Chat::makePrivate()->createConversation(Auth::user(), $user);

            $message = ChatMessage::createMessage($request, $conversation);

            [$messageKey,$expire] = Highker::getCacheKey('user:message', 'add-message-count', [now()->toDateString(), $user->id]);

            // 今日私信次数 +1
            Redis::incr($messageKey);
            Redis::expire($messageKey, $expire);

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();

            return $this->fail($e->getMessage());
        }

        return $this->success(ChatConversationResource::make($message), '创建私聊对话成功');
    }

    /**
     * 单向删除(离开当前对话).
     *
     * @throws AuthorizationException
     *
     * @return JsonResource|JsonResponse
     */
    public function destroy(ChatConversation $conversation)
    {
        $this->authorize('delete', $conversation);

        Chat::conversation($conversation)->leaveConversation(Auth::user());

        return $this->success(null, '对话删除成功');
    }
}
