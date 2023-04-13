<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class AdCategory.
 *
 * @property int    $id         id
 * @property string $name       名称
 * @property string $with       宽度
 * @property string $height     高度
 * @property bool   $status     是否开启
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class AdCategory extends BaseModel
{
    protected $fillable = [
        'name',
        'width',
        'height',
        'status',
    ];
}
