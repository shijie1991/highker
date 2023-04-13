<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Notifications;

use HighKer\Core\Enum\NoticeEvent;
use HighKer\Core\Enum\NoticeResourceType;
use HighKer\Core\Enum\NoticeTargetType;
use HighKer\Core\Enum\NoticeTriggerType;
use HighKer\Core\Enum\NoticeType;
use HighKer\Core\Models\Comment;
use HighKer\Core\Models\Feed;
use HighKer\Core\Models\User;

class CommentNotifications extends BaseUserNotifications
{
    private User $trigger;
    private Feed $target;
    private Comment $resource;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $trigger, Feed $target, Comment $resource)
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
                    'image'   => optional($this->target->images()->first())->path,
                ],
                'type' => NoticeTargetType::FEED,
            ],
            'resource' => [
                'id'   => $this->resource->id,
                'body' => [
                    'content' => $this->resource->format_content,
                ],
                'type' => NoticeResourceType::COMMENT,
            ],
            'event'       => NoticeEvent::FEED_COMMENT,
            'notice_type' => NoticeType::INTERACTIVE,
        ];
    }
}
