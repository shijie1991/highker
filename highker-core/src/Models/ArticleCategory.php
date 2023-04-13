<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Dcat\Admin\Exception\AdminException;
use Dcat\Admin\Traits\ModelTree;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Request;

/**
 * Class ArticleCategory.
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
class ArticleCategory extends BaseModel
{
    use ModelTree;

    protected $fillable = ['name', 'is_directory', 'level', 'cate_path'];
    protected $casts = ['is_directory' => 'boolean'];

    // 标题字段名称，默认值为 title
    protected $titleColumn = 'name';

    /**
     * @throws AdminException
     */
    protected static function boot()
    {
        parent::boot();

        // 监听 Category 的创建事件，用于初始化 path 和 level 字段值
        static::saving(function (ArticleCategory $category) {
            $parentColumn = $category->getParentColumn();
            if (Request::has($parentColumn) && Request::input($parentColumn) == $category->getKey()) {
                throw new Exception(trans('admin.parent_select_error'));
            }

            // 如果创建的是一个根类目
            if (!$category->parent_id) {
                // 将层级设为 0
                $category->level = 0;
                // 将 path 设为 -
                $category->cate_path = '-';
                // 是否根目录
                $category->is_directory = 0;
            } else {
                // 将层级设为父类目的层级 + 1
                $category->level = $category->parent->level + 1;
                // 将 path 值设为父类目的 path 追加父类目 ID 以及最后跟上一个 - 分隔符
                $category->cate_path = $category->parent->cate_path.$category->parent_id.'-';
                // 是否根目录
                $category->is_directory = 1;
            }

            if (Request::has('_order')) {
                $order = Request::input('_order');
                Request::offsetUnset('_order');
                $order = json_decode($order, true);
                static::saveOrder($order);

                return false;
            }

            return $category;
        });
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(ArticleCategory::class, 'parent_id');
    }

    // 定一个一个访问器，获取所有祖先类目的 ID 值
    public function getPathIdsAttribute()
    {
        // trim($str, '-') 将字符串两端的 - 符号去除
        // explode() 将字符串以 - 为分隔切割为数组
        // 最后 array_filter 将数组中的空值移除
        return array_filter(explode('-', trim($this->cate_path, '-')));
    }

    // 定义一个访问器，获取所有祖先类目并按层级排序
    public function getAncestorsAttribute()
    {
        return ArticleCategory::query()
            // 使用上面的访问器获取所有祖先类目 ID
            ->whereIn('id', $this->path_ids)
            // 按层级排序
            ->orderBy('level')
            ->get()
        ;
    }

    // 定义一个访问器，获取以 - 为分隔的所有祖先类目名称以及当前类目的名称
    public function getFullNameAttribute()
    {
        return $this->ancestors  // 获取所有祖先类目
            ->pluck('name') // 取出所有祖先类目的 name 字段作为一个数组
            ->push($this->name) // 将当前类目的 name 字段值加到数组的末尾
            ->implode(' - ') // 用 - 符号将数组的值组装成一个字符串
            ;
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id', 'id');
    }
}
