<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Dcat\Admin\Traits\ModelTree;

/**
 * @property int    $id
 * @property string $name       分类名称
 * @property int    $parent_id  上级 ID
 * @property int    $order      排序
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class TopicGroup extends BaseModel
{
    use ModelTree;

    protected $fillable = ['name'];

    // 标题字段名称，默认值为 title
    protected $titleColumn = 'name';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class, 'group_id');
    }
}
