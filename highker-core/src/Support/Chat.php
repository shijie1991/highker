<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support;

use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\ChatConversation;
use HighKer\Core\Models\ChatMessageNotification;
use HighKer\Core\Models\Traits\ChatParticipantsTraits;
use HighKer\Core\Models\User;
use HighKer\Core\Services\ChatConversationService;
use HighKer\Core\Services\ChatMessageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Chat.
 */
class Chat
{
    use ChatParticipantsTraits;

    protected ChatMessageService $messageService;
    protected ChatConversationService $conversationService;
    protected ChatMessageNotification $messageNotification;

    public function __construct(
        ChatMessageService $messageService,
        ChatConversationService $conversationService,
        ChatMessageNotification $messageNotification
    ) {
        $this->messageService = $messageService;
        $this->conversationService = $conversationService;
        $this->messageNotification = $messageNotification;
    }

    /**
     * 创建一个新的对话.
     *
     * @throws HighKerException
     */
    public function createConversation(User $sender, User $receiver, array $data = null): Builder|Model
    {
        return $this->conversationService->start([
            'sender'   => $sender,
            'receiver' => $receiver,
            'data'     => $data,
            'private'  => $this->conversationService->privateConversation,
        ]);
    }

    /**
     * 提前设置 对话为 私信
     *
     * @return $this
     */
    public function makePrivate(): Chat
    {
        $this->conversationService->privateConversation = true;

        return $this;
    }

    /**
     * 设置消息.
     */
    public function message(string $message): ChatMessageService
    {
        return $this->messageService->setMessage($message);
    }

    /**
     * 获取消息.
     */
    public function messages(): ChatMessageService
    {
        return $this->messageService;
    }

    /**
     * 设置对话.
     */
    public function conversation(ChatConversation $conversation): ChatConversationService
    {
        return $this->conversationService->setConversation($conversation);
    }

    /**
     * 获取对话.
     */
    public function conversations(): ChatConversationService
    {
        return $this->conversationService;
    }
}
