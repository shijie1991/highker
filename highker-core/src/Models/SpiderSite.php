<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class SpiderFakerUser.
 *
 * @property string extra
 * @property string page
 */
class SpiderSite extends BaseModel
{
    protected $fillable = [
        'name',
        'type',
        'url',
        'page',
        'extra',
    ];
}
