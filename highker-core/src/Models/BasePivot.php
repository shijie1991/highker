<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BasePivot extends Pivot
{
    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);

        // 设置分页时 默认每页的数量
        $this->setPerPage(config('core.page_size'));
    }
}
