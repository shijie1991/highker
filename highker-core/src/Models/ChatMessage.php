<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\ChatMessageType;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Observers\ChatMessageObserver;
use HighKer\Core\Support\Facades\Chat;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Utils\ImageUtils;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class ChatMessage.
 *
 * @property int              id
 * @property int              conversation_id
 * @property int              sender
 * @property string           content
 * @property int              type
 * @property string           extra
 * @property int              created_at
 * @property int              updated_at
 * @property ChatConversation conversation
 * @property User             sender_user
 */
class ChatMessage extends BaseModel
{
    protected $fillable = [
        'sender',
        'type',
        'content',
        'extra',
    ];

    protected $casts = [
        'type'  => 'int',
        'extra' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::observe(ChatMessageObserver::class);
    }

    public function formatContent(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->type == ChatMessageType::IMAGE) {
                    return '[图片]';
                }

                if ($this->type == ChatMessageType::VOICE) {
                    return '[语音]';
                }

                return Str::limit(str_replace(PHP_EOL, '', $this->content), 40);
            }
        );
    }

    /**
     * @throws HighKerException
     *
     * @return Model
     */
    public static function createMessage(Request $request, ChatConversation $conversation)
    {
        $content = $request->input('content');
        $type = ChatMessageType::TEXT;

        if ($request->hasFile('image')) {
            // 获取 图片 尺寸
            [$width, $height] = ImageUtils::getSize($request->file('image'));

            if ($path = Storage::putFile(Highker::uploadDir('chat'), $request->file('image'))) {
                $type = ChatMessageType::IMAGE;
                $extra = [
                    'image' => [
                        'width'  => $width,
                        'height' => $height,
                    ],
                ];
                $content = $path;
            }
        }

        if ($request->hasFile('voice')) {
            $type = ChatMessageType::VOICE;
            $extra = ['duration' => $request->input('duration')];
            $content = Storage::putFile(Highker::uploadDir('chat'), $request->file('voice'));
        }

        try {
            $message = Chat::message($content)
                ->type($type)
                ->from(Auth::user())
                ->to($conversation)
                ->extra($extra ?? null)
                ->send()
            ;
        } catch (Throwable $e) {
            throw new HighKerException('消息发送失败');
        }

        // 如果是盲盒消息
        if (!$message->conversation->private) {
            $message->unsetRelation('sender_user');
            $message->loadMissing(['secret_user']);
            $message->secret_user->makeHidden(['id', 'name', 'avatar', 'status', 'gender', 'level', 'is_vip']);
            $message->secret_user->makevisible(['fake_avatar', 'fake_name']);
        }

        return $message->makeHidden('conversation');
    }

    /**
     * 像对话 添加一条消息.
     *
     * @param null $extra
     */
    public function send(ChatConversation $conversation, User $sender, string $content, string $type = ChatMessageType::TEXT, $extra = null): Model
    {
        /** @var ChatMessage $message */
        $message = $conversation->messages()->create([
            'content' => $content,
            'sender'  => $sender->id,
            'type'    => $type,
            'extra'   => $extra,
        ]);

        // 创建或更新通知
        ChatMessageNotification::createOrUpdateNotifications($message, $conversation);

        return $message;
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /**
     * 私信对话 用户.
     */
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
}
