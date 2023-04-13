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

class SystemNotifications extends BaseUserNotifications
{
    private Model $trigger;
    private string $resource;

    /**
     * @param $resource
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
                'type' => NoticeTriggerType::USER,
            ],

            'resource' => [
                'body' => [
                    'content' => $this->resource,
                ],
            ],
            'event'       => NoticeEvent::SYSTEM_NOTICE,
            'notice_type' => NoticeType::SYSTEM,
        ];
    }
}
