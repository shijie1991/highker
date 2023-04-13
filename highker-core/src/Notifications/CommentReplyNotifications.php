<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Notifications;

use HighKer\Core\Enum\CommentLevel;
use HighKer\Core\Enum\NoticeEvent;
use HighKer\Core\Enum\NoticeResourceType;
use HighKer\Core\Enum\NoticeTargetType;
use HighKer\Core\Enum\NoticeTriggerType;
use HighKer\Core\Enum\NoticeType;
use HighKer\Core\Models\Comment;
use HighKer\Core\Models\User;

class CommentReplyNotifications extends BaseUserNotifications
{
    private User $trigger;
    private Comment $target;
    private Comment $resource;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $trigger, Comment $target, Comment $resource)
    {
        $this->trigger = $trigger;
        $this->target = $target;
        $this->resource = $resource;
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
                'type' => NoticeTriggerType::USER,
            ],

            'target' => [
                'id'   => $this->target->id,
                'body' => [
                    'content' => $this->target->format_content,
                ],
                'users' => [
                    [
                        'id'     => optional($this->target->user)->id,
                        'name'   => optional($this->target->user)->name,
                        'avatar' => optional($this->target->user)->avatar,
                    ],
                ],
                'type' => $this->getTargetType(),
            ],

            'resource' => [
                'id'   => $this->resource->id,
                'body' => [
                    'content' => $this->resource->format_content,
                ],
                'type' => NoticeResourceType::COMMENT_REPLY,
            ],

            'event'       => NoticeEvent::COMMENT_REPLY,
            'notice_type' => NoticeType::INTERACTIVE,
        ];
    }

    protected function getTargetType(): int
    {
        return $this->target->level != CommentLevel::COMMENT ? NoticeTargetType::COMMENT_REPLY : NoticeTargetType::COMMENT;
    }
}
