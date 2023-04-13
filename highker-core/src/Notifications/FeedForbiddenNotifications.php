<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Notifications;

use HighKer\Core\Enum\NoticeEvent;
use HighKer\Core\Enum\NoticeTriggerType;
use HighKer\Core\Enum\NoticeType;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FeedForbiddenNotifications.
 *
 * 动态屏蔽消息通知
 */
class FeedForbiddenNotifications extends BaseUserNotifications
{
    private Model $trigger;
    private string $resource;

    /**
     * Create a new notification instance.
     */
    public function __construct(Model $trigger, $resource)
    {
        $this->trigger = $trigger;
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
                'type' => NoticeTriggerType::SYSTEM,
            ],

            // 'target' => [
            //     'id'   => $this->target->id,
            //     'body' => [
            //         'content' => $this->target->format_content,
            //         'image'   => optional($this->target->images()->first())->path,
            //     ],
            //     'type' => NoticeTargetType::FEED,
            // ],

            'resource' => [
                'body' => [
                    'content' => $this->resource,
                ],
            ],

            'event'       => NoticeEvent::FEED_FORBIDDEN,
            'notice_type' => NoticeType::SYSTEM,
        ];
    }
}
