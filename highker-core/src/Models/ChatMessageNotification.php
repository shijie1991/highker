<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class ChatMessageNotification.
 *
 * @property int    id
 * @property int    conversation_id
 * @property int    message_id
 * @property int    sender
 * @property int    receiver
 * @property string private
 * @property int    unread_count
 * @property string read_at
 * @property string rejoined_at
 * @property string created_at
 * @property string updated_at
 * @property string deleted_at
 */
class ChatMessageNotification extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'message_id',
        'sender',
        'receiver',
        'private',
        'unread_count',
    ];

    protected $casts = [
        'private' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    /**
     * 创建或更新消息通知.
     */
    public static function createOrUpdateNotifications(ChatMessage $message, ChatConversation $conversation)
    {
        // 如果是盲盒消息 未回复的情况下不给对方创建 已读消息
        if (!$conversation->private && !$conversation->isReply()) {
            $between = [
                ['sender' => $conversation->sender_user->id, 'receiver' => $conversation->receiver_user->id],
            ];
        } else {
            $between = [
                ['sender' => $conversation->sender_user->id, 'receiver' => $conversation->receiver_user->id],
                ['sender' => $conversation->receiver_user->id, 'receiver' => $conversation->sender_user->id],
            ];
        }

        foreach ($between as $user) {
            $data = [
                'message_id'   => $message->id,
                'unread_count' => DB::raw('`unread_count`+1'),
                'private'      => $conversation->private,
            ];

            // 如果是发送者 则不增加未读数量
            if ($message->sender == $user['receiver']) {
                Arr::pull($data, 'unread_count');
            }

            $where = [
                'conversation_id' => $conversation->id,
                'sender'          => $user['sender'],
                'receiver'        => $user['receiver'],
            ];

            ChatMessageNotification::query()->updateOrCreate($where, $data);
        }
    }

    /**
     * 获取 最近的一条消息.
     */
    public function last_message(): HasOne
    {
        return $this->hasOne(ChatMessage::class, 'id', 'message_id');
    }

    public function sender_user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'sender');
    }

    /**
     * 盲盒匿名用户.
     */
    public function secret_user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'sender');
    }

    public function receiver_user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'receiver');
    }
}
