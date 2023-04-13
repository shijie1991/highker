<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Notifications;

use HighKer\Core\Channels\WebsocketChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BaseSaeNotifications extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return [WebsocketChannel::class];
    }
}
