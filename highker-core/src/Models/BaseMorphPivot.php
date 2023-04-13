<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class BaseMorphPivot extends MorphPivot
{
    /**
     * BaseMorphPivot constructor.
     */
    public function __construct(array $attributes = [])
    {
        // 定义数据库连接名称
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);

        // 设置分页时 默认每页的数量
        $this->setPerPage(config('core.page_size'));
    }
}
