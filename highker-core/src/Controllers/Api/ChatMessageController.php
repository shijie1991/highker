<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use Exception;
use HighKer\Core\Enum\ResponseCode;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\ChatConversation;
use HighKer\Core\Models\ChatMessage;
use HighKer\Core\Models\User;
use HighKer\Core\Requests\ChatMessageCreateRequest;
use HighKer\Core\Resources\ChatMessageCollectionResource;
use HighKer\Core\Resources\ChatMessageResource;
use HighKer\Core\Support\Facades\Chat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ChatMessageController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['forbidden_user'])->except(['index']);
        $this->middleware(['locked_user'])->only(['store']);
    }

    /**
     * 消息列表.
     *
     * @return JsonResource|JsonResponse
     */
    public function index(ChatConversation $conversation)
    {
        // 获取对话的另一个人
        $counterpartKey = $conversation->sender == Auth::id() ? 'receiver' : 'sender';

        $counterpart = User::query()->findOrFail($conversation->{$counterpartKey});

        // 查看 聊天记录 并设置为已读
        $message = Chat::conversation($conversation)->from(Auth::user())->readAll()->getMessages();

        return $this->success(ChatMessageCollectionResource::make($message)->additional(['name' => $counterpart->name]));
    }

    /**
     * @throws HighKerException
     * @throws Exception
     */
    public function store(ChatMessageCreateRequest $request, ChatConversation $conversation)
    {
        // 如果是私信对方未回复的情况下 设置条件不允许一直发送
        if ($conversation->private && $conversation->sender == Auth::id()) {
            // 如果对方没有回复
            if (!Chat::conversation($conversation)->from(Auth::user())->isReply()) {
                // 如果发送超过 3 条消息 提醒
                if (Chat::conversation($conversation)->from(Auth::user())->messageCount() >= 3) {
                    throw new HighKerException(ResponseCode::MAP[ResponseCode::MESSAGE_WAITING_REPLY], ResponseCode::MESSAGE_WAITING_REPLY);
                }
            }
        }

        $message = ChatMessage::createMessage($request, $conversation);

        return $this->success(ChatMessageResource::make($message), '消息发送成功');
    }

    public function read(ChatConversation $conversation)
    {
        // 设置为已读
        Chat::conversation($conversation)->from(Auth::user())->readAll();

        return $this->success(null, '更新成功');
    }
}
