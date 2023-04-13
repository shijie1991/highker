<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Models\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;

/**
 * Class BaseModel.
 */
class BaseModel extends EloquentModel
{
    use HasDateTimeFormatter;

    /**
     * BaseModel constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);

        // 设置分页时 默认每页的数量
        $this->setPerPage(config('core.page_size'));
    }

    /**
     * 获取与模型关联的表名。
     */
    public function getTable(): string
    {
        return $this->table ?? Str::snake(class_basename($this));
    }
}
