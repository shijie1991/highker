<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class SensitiveWords.
 *
 * @property int    $id
 * @property string $name       关键词名称
 * @property int    $status     是否开启
 * @property int    $count      命中次数
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class SensitiveWords extends BaseModel
{
    protected $fillable = [
        'name',
        'status',
        'count',
    ];
}
