<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Notifications;

use HighKer\Core\Enum\NoticeEvent;
use HighKer\Core\Enum\NoticeType;
use Illuminate\Notifications\DatabaseNotification;

class UserSystemNotifications extends BaseUserNotifications
{
    private DatabaseNotification $systemNotice;

    /**
     * Create a new notification instance.
     */
    public function __construct(DatabaseNotification $systemNotice)
    {
        $this->systemNotice = $systemNotice;
    }

    /** @noinspection PhpUndefinedFieldInspection */
    public function toArray($notifiable): array
    {
        return [
            'trigger'     => $this->systemNotice->data['trigger'],
            'resource'    => $this->systemNotice->data['resource'],
            'event'       => NoticeEvent::SYSTEM_NOTICE,
            'notice_type' => NoticeType::SYSTEM,
            'last_at'     => $this->systemNotice->created_at,
        ];
    }
}
