<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Models\Traits\HasDateTimeFormatter;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @property int id
 * @property int notice_type
 * @property int event
 * @property int notifiable_type
 * @property int notifiable_id
 * @property array data
 * @property int read_at
 * @property int last_at
 * @property int created_at
 * @property int updated_at
 */
class Notification extends DatabaseNotification
{
    use HasDateTimeFormatter;
}
