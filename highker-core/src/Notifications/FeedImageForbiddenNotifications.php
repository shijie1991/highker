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
use HighKer\Core\Models\Feed;

/**
 * Class FeedForbiddenNotifications.
 *
 * 动态图片屏蔽消息通知
 */
class FeedImageForbiddenNotifications extends BaseUserNotifications
{
    private Feed $target;
    private string $resource;

    /**
     * Create a new notification instance.
     */
    public function __construct(Feed $target, string $resource = '')
    {
        $this->target = $target;
        $this->resource = $resource;
    }

    public function toArray($notifiable): array
    {
        return [
            'trigger' => [
                'type' => NoticeTriggerType::SYSTEM,
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
                'body' => [
                    'content' => $this->resource,
                ],
            ],

            'event'       => NoticeEvent::FEED_IMAGE_FORBIDDEN,
            'notice_type' => NoticeType::SYSTEM,
        ];
    }
}
