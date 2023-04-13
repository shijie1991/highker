<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Resources;

use HighKer\Core\Models\ChatMessageNotification;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class ChatConversationResource.
 */
class ChatConversationCollectionResource extends ResourceCollection
{
    public function toArray($request): array
    {
        // 格式化 内容
        $this->collection->each(function ($item) {
            /* @var ChatMessageNotification $item */
            if ($item->relationLoaded('secret_user')) {
                $item->secret_user->makeHidden(['id', 'name', 'avatar', 'status', 'gender', 'level', 'is_vip']);
                $item->secret_user->makevisible(['fake_avatar', 'fake_name']);
            }

            $item->last_message->makeHidden('content');
            $item->last_message->append(['format_content']);
        });

        return [
            'data' => $this->collection,
        ];
    }
}
