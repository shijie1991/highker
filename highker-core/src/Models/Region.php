<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class Region.
 *
 * @property int id
 * @property int name
 * @property int level
 * @property int parent_id
 * @property int code
 */
class Region extends BaseModel
{
    public function parents()
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Region::class, 'parent_id');
    }
}
