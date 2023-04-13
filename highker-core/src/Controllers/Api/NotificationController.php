<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use HighKer\Core\Enum\NoticeType;
use HighKer\Core\Resources\CommonResource;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseController
{
    public function system()
    {
        $notifications = Auth::user()->notifications()->where('notice_type', NoticeType::SYSTEM)->simplePaginate();

        Auth::user()->unreadNotifications()->where('notice_type', NoticeType::SYSTEM)->update(['read_at' => now()]);

        return $this->success(CommonResource::collection($notifications));
    }

    public function interactive()
    {
        $notifications = Auth::user()->notifications()->where('notice_type', NoticeType::INTERACTIVE)->simplePaginate();

        Auth::user()->unreadNotifications()->where('notice_type', NoticeType::INTERACTIVE)->update(['read_at' => now()]);

        return $this->success(CommonResource::collection($notifications));
    }
}
