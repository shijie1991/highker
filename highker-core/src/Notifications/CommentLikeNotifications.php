<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Notifications;

use HighKer\Core\Enum\NoticeEvent;
use HighKer\Core\Enum\NoticeTargetType;
use HighKer\Core\Enum\NoticeTriggerType;
use HighKer\Core\Enum\NoticeType;
use HighKer\Core\Models\Comment;
use HighKer\Core\Models\User;

class CommentLikeNotifications extends BaseUserNotifications
{
    private User $trigger;
    private Comment $target;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $trigger, Comment $target)
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
                'type' => NoticeTriggerType::USER,
            ],

            'target' => [
                'id'   => $this->target->id,
                'body' => [
                    'content' => $this->target->format_content,
                    'image'   => optional($this->target->images()->first())->path,
                ],
                'type' => NoticeTargetType::COMMENT,
            ],
            'event'       => NoticeEvent::COMMENT_LIKE,
            'notice_type' => NoticeType::INTERACTIVE,
        ];
    }
}
