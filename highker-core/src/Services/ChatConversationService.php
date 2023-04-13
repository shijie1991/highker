<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Services;

use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\ChatConversation;
use HighKer\Core\Models\Traits\ChatParticipantsTraits;
use HighKer\Core\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ChatConversationService.
 */
class ChatConversationService
{
    use ChatParticipantsTraits;

    public ChatConversation $conversation;

    public bool $privateConversation = false;

    protected array $filters = [];

    public function __construct(ChatConversation $conversation)
    {
        $this->conversation = $conversation;
    }

    /**
     * 创建 聊天.
     *
     * @throws HighKerException
     */
    public function start(array $payload): Builder|Model
    {
        return $this->conversation->start($payload);
    }

    public function getById($id)
    {
        return $this->conversation->query()->find($id);
    }

    /**
     * 设置是否为私信
     *
     * @return $this
     */
    public function isPrivate(bool $isPrivate = true): ChatConversationService
    {
        $this->filters['private'] = $isPrivate;

        return $this;
    }

    /**
     * 设置当前对话.
     *
     * @param $conversation
     *
     * @return $this
     */
    public function setConversation($conversation): ChatConversationService
    {
        $this->conversation = $conversation;

        return $this;
    }

    /**
     * 获取 对话列表.
     */
    public function get(): Paginator
    {
        return $this->conversation->getConversationsList($this->sender, ['filters' => $this->filters]);
    }

    /**
     * 获取 两个用户之间的 对话.
     *
     * @return null|Builder|Model|object
     */
    public function conversationsBetweenUser(User|Authenticatable $sender, User|Authenticatable $receiver)
    {
        // or 语句 优化
        /** @var \Illuminate\Database\Query\Builder $union */
        $union = $this->conversation->query()->where('private', true)
            ->where('sender', $sender->id)
            ->where('receiver', $receiver->id)
        ;

        return $this->conversation->query()->where('private', true)
            ->where('sender', $receiver->id)
            ->where('receiver', $sender->id)->union($union)
            ->first()
        ;
    }

    /**
     * 离开当前对话.
     */
    public function leaveConversation(Authenticatable|User $user): ChatConversation
    {
        return $this->conversation->leaveConversation($user);
    }

    /**
     * 获取对话信息.
     */
    public function getMessages()
    {
        return $this->conversation->getMessages($this->sender);
    }

    /**
     * 获取未读数量.
     */
    public function unreadCount(): int
    {
        return $this->conversation->unReadNotificationsAllCount($this->sender, ['filters' => $this->filters]);
    }

    /**
     * 将对话中的所有消息标记为已读.
     *
     * @return $this
     */
    public function readAll(): ChatConversationService
    {
        $this->conversation->readAll($this->sender);

        return $this;
    }

    /**
     * 对方是否回复.
     *
     * @return bool
     */
    public function isReply()
    {
        return $this->conversation->isReply();
    }

    /**
     * 获取发送的信息数量.
     *
     * @return int
     */
    public function messageCount()
    {
        return $this->conversation->messageCount();
    }
}
