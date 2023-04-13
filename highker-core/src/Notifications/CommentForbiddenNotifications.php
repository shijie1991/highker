<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Notifications;

use HighKer\Core\Enum\CommentLevel;
use HighKer\Core\Enum\NoticeEvent;
use HighKer\Core\Enum\NoticeTargetType;
use HighKer\Core\Enum\NoticeTriggerType;
use HighKer\Core\Enum\NoticeType;
use HighKer\Core\Models\Comment;
use Illuminate\Database\Eloquent\Model;

class CommentForbiddenNotifications extends BaseUserNotifications
{
    private Model $trigger;
    private Comment $target;

    /**
     * Create a new notification instance.
     */
    public function __construct(Model $trigger, Comment $target)
    {
        $this->trigger = $trigger;
        $this->target = $target;
    }

    public function toArray($notifiable): array
    {
        return [
            'trigger' => [
                'users' => [
                    [
                        'id'     => $this->trigger->id,
                        'name'   => $this->trigger->name,
                        'avatar' => $this->trigger->avatar,
                    ],
                ],
                'type' => NoticeTriggerType::ADMIN,
            ],

            'target' => [
                'id'   => $this->target->id,
                'body' => [
                    'content' => $this->target->format_content,
                    'image'   => optional($this->target->images()->first())->path,
                ],
                'type' => $this->getTargetType(),
            ],
            'event'       => NoticeEvent::COMMENT_FORBIDDEN,
            'notice_type' => NoticeType::SYSTEM,
        ];
    }

    protected function getTargetType()
    {
        return $this->target->level != CommentLevel::COMMENT ? NoticeTargetType::COMMENT_REPLY : NoticeTargetType::COMMENT;
    }
}
