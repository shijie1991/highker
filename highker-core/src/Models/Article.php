<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

/**
 * Class Article.
 *
 * @property int    $id
 * @property string $name         分类名称
 * @property int    $parent_id    上级 ID
 * @property int    $order        排序
 * @property bool   $is_directory 是否为目录
 * @property int    $level        记录分类层级
 * @property string $cate_path    记录分类层级路径
 * @property string $created_at   创建时间
 * @property string $updated_at   更新时间
 */
class Article extends BaseModel
{
    protected $fillable = ['title', 'content'];

    protected static function boot()
    {
        parent::boot();
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class);
    }
}
