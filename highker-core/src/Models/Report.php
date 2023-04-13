<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Report.
 *
 * @property int id
 * @property int user_id
 * @property int reason
 * @property int resources_type
 * @property int resources_id
 * @property int content
 * @property int created_at
 * @property int updated_at
 */
class Report extends BaseModel
{
    protected static function boot()
    {
        parent::boot();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
