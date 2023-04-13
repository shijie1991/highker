<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class spider_faker_user.
 */
class SpiderFakerUser extends BaseModel
{
    protected $fillable = [
        'user_id',
        'md5_user_id',
        'name',
        'gender',
        'avatar',
        'description',
        'labels',
        'source',
        'birthday',
        'source_created_at',
    ];
}
