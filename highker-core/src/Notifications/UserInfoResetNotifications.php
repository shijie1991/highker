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
 * 用户信息重置通知.
 */
class UserInfoResetNotifications extends BaseUserNotifications
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

            'resource' => [
                'body' => [
                    'content' => $this->resource,
                ],
            ],
            'event'       => NoticeEvent::USER_INFO_RESET,
            'notice_type' => NoticeType::SYSTEM,
        ];
    }
}
