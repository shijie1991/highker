<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int user_id
 * @property int visitor_id
 * @property int visit_count
 * @property int created_at
 * @property int updated_at
 */
class UserVisit extends BaseModel
{
    protected $fillable = ['user_id', 'visitor_id', 'visit_count'];

    protected static function boot()
    {
        parent::boot();
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
