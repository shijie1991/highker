<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\UserTask;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Support\Facades\Chat;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class ChatConversation.
 *
 * @property int         id
 * @property int         sender
 * @property int         receiver
 * @property int         private
 * @property string      data
 * @property string      created_at
 * @property string      updated_at
 * @property ChatMessage messages
 * @property User        sender_user
 * @property User        receiver_user
 */
class ChatConversation extends BaseModel
{
    protected $fillable = ['sender', 'receiver', 'data', 'private'];

    protected $casts = [
        'data'    => 'array',
        'private' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function (ChatConversation $conversation) {
            // 如果是私信
            if ($conversation->private && $conversation->sender == Auth::id()) {
                // 新手任务 发送私信
                TaskLog::onceTask(UserTask::PRIVATE_MESSAGE);
            }
        });
    }

    /**
     * 开始一个新的对话.
     *
     * @return Builder|Model
     *
     * @throws HighKerException
     */
    public function start(array $payload)
    {
        if (!$payload['sender'] || !$payload['receiver']) {
            throw new HighKerException('无效的参与用户');
        }

        if ($payload['private']) {
            $this->privateConversationIsExist($payload['sender'], $payload['receiver']);
        }

        return $this->query()->create([
            'sender'   => $payload['sender']->id,
            'receiver' => $payload['receiver']->id,
            'data'     => $payload['data'],
            'private'  => $payload['private'],
        ]);
    }

    /**
     * 离开当前对话.
     *
     * @return $this
     */
    public function leaveConversation(User $user): ChatConversation
    {
        $user->leaveConversation($this->getKey());

        return $this;
    }

    /**
     * 用户 是否离开 当前对话.
     *
     * @return null|Builder|\Illuminate\Database\Query\Builder|Model|object
     */
    public function isLeave(User|Authenticatable $sender)
    {
        return ChatMessageNotification::onlyTrashed()
            ->where('conversation_id', $this->id)
            ->where('receiver', $sender->id)
            ->first()
        ;
    }

    /**
     * 重新加入 当前对话.
     */
    public function reJoin(User|Authenticatable $sender)
    {
        return ChatMessageNotification::onlyTrashed()
            ->where('conversation_id', $this->id)
            ->where('receiver', $sender->id)
            ->update(['deleted_at' => null, 'rejoined_at' => $this->freshTimestamp()])
        ;
    }

    public function isRejoin(User|Authenticatable $sender)
    {
        return ChatMessageNotification::query()
            ->where('conversation_id', $this->id)
            ->where('receiver', $sender->id)
            ->value('rejoined_at')
        ;
    }

    /**
     * 获取 用户 对话列表.
     */
    public function getConversationsList(Model $sender, array $options): Paginator
    {
        return ChatMessageNotification::query()
            ->with(['last_message'])
            ->where('receiver', $sender->id)
            ->where('private', $options['filters']['private'])
            ->when($options['filters']['private'] === false, function (Builder $query) {
                return $query->with(['secret_user']);
            }, function (Builder $query) {
                return $query->with(['sender_user']);
            })
            ->orderByDesc('unread_count')
            ->orderByDesc('updated_at')
            ->simplePaginate()
        ;
    }

    /**
     * 获取对话消息.
     */
    public function getMessages(User $sender): CursorPaginator
    {
        // 私信
        if ($this->private) {
            // 是否退出了对话
            $isLeave = $this->isLeave(Auth::user());

            // 如果退出了对话 则重新加入对话
            if ($isLeave) {
                $this->reJoin(Auth::user());
            }
        }

        // 获取 重新加入对话的时间
        $rejoinedAt = $this->private ? $this->isRejoin($sender) : null;

        return $this->messages()
            ->when($this->private, function (Builder $query) {
                return $query->with(['sender_user']);
            }, function (Builder $query) {
                return $query->with(['secret_user']);
            })
            ->orderByDesc('chat_message.id')
            ->when($rejoinedAt, function (Builder $query) use ($rejoinedAt) {
                return $query->where('created_at', '>', $rejoinedAt);
            })
            ->cursorPaginate()
        ;
    }

    /**
     * 获取所有对话未读数量.
     */
    public function unReadNotificationsAllCount(User $sender, array $options): int
    {
        return ChatMessageNotification::query()
            ->where('receiver', $sender->id)
            ->when(isset($options['filters']['private']), function (Builder $query) use ($options) {
                return $query->where('private', $options['filters']['private']);
            })
            ->value(DB::Raw('sum(unread_count)')) ?? 0;
    }

    /**
     * 将对话消息标记为已读.
     */
    public function readAll(User $sender): int
    {
        return ChatMessageNotification::withoutTimestamps(
            function () use ($sender) {
                return ChatMessageNotification::query()
                    ->where('conversation_id', $this->getKey())
                    ->where('receiver', $sender->id)
                    ->update(['unread_count' => 0, 'read_at' => $this->freshTimestamp()])
                ;
            }
        );
    }

    /**
     * 对方是否回复.
     */
    public function isReply()
    {
        return $this->messages()->where('sender', $this->receiver)->exists();
    }

    /**
     * @return int
     */
    public function messageCount()
    {
        return $this->messages()->where('sender', $this->sender)->count();
    }

    /**
     * 两用户之间是否曾经私信过.
     *
     * @throws HighKerException
     */
    private function privateConversationIsExist(User $sender, User $receiver)
    {
        $exist = Chat::conversations()->conversationsBetweenUser($sender, $receiver);

        if (!is_null($exist)) {
            throw new HighKerException('已私聊过该用户,请从列表进入查看');
        }
    }

    public function sender_user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'sender');
    }

    public function receiver_user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'receiver');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }
}
