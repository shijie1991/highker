<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Resources;

use HighKer\Core\Models\ChatMessage;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChatMessageCollectionResource extends ResourceCollection
{
    public function toArray($request): array
    {
        // 格式化 内容
        $this->collection->each(function ($item) {
            /* @var ChatMessage $item */
            if ($item->relationLoaded('secret_user')) {
                $item->secret_user->makeHidden(['id', 'name', 'avatar', 'status', 'gender', 'level', 'is_vip']);
                $item->secret_user->makevisible(['fake_avatar', 'fake_name']);
            }
        });

        return [
            // 反转数组 最新的数据在最下面 前端直接循环
            'data' => $this->collection->reverse()->values(),
        ];
    }
}
