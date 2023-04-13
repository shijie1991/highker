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
use HighKer\Core\Models\User;

class FollowNotifications extends BaseUserNotifications
{
    private User $trigger;
    private User $target;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $trigger, User $target)
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
                'id'     => $this->target->id,
                'name'   => $this->target->name,
                'avatar' => $this->target->avatar,
                'type'   => NoticeTargetType::USER,
            ],
            'event'       => NoticeEvent::USER_FOLLOW,
            'notice_type' => NoticeType::INTERACTIVE,
        ];
    }
}
