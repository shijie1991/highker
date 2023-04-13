<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Services;

use HighKer\Core\Enum\ChatMessageType;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\ChatMessage;
use HighKer\Core\Models\Traits\ChatParticipantsTraits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ChatMessageService
{
    use ChatParticipantsTraits;

    protected int $type = ChatMessageType::TEXT;

    protected string $content;

    protected ?array $extra = null;

    protected ChatMessage $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    /**
     * 发送消息.
     *
     * @throws HighKerException
     */
    public function send(): Model
    {
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$this->sender) {
            throw new HighKerException('未设置发送者');
        }

        if (strlen($this->content) == 0) {
            throw new HighKerException('未设置发送内容');
        }

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$this->conversations) {
            throw new HighKerException('未设置对话');
        }

        if (!in_array($this->type, ChatMessageType::LIST)) {
            throw new HighKerException('消息类型错误');
        }

        $isLeave = $this->conversations->isLeave($this->sender);

        if ($isLeave) {
            throw new HighKerException('已经离开当前对话');
        }

        return $this->message->send($this->conversations, $this->sender, $this->content, $this->type, $this->extra);
    }

    /**
     * 设置消息内容.
     *
     * @param $message
     *
     * @return $this
     */
    public function setMessage($message): ChatMessageService
    {
        if (is_object($message)) {
            $this->message = $message;
        } else {
            $this->content = $message;
        }

        return $this;
    }

    /**
     * 设置消息类型.
     *
     * @param $type
     *
     * @return $this
     */
    public function type($type): ChatMessageService
    {
        $this->type = $type;

        return $this;
    }

    /**
     * 设置消息额外信息.
     *
     * @param $extra
     *
     * @return $this
     */
    public function extra($extra): ChatMessageService
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * 根据 ID 获取消息.
     *
     * @param $id
     *
     * @return null|array|Builder|Collection|Model
     */
    public function getById($id)
    {
        return $this->message->query()->findOrFail($id);
    }
}
