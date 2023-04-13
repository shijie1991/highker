<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AdCategory.
 *
 * @property string $name        名称
 * @property int    $category_id 广告位 ID
 * @property bool   $status      是否开启
 * @property string $image       广告图片地址
 * @property string $url         广告链接
 * @property bool   $target      打开方式 0:当前页面 1:新页面
 * @property string $before      开始时间
 * @property string $after       结束时间
 * @property string $created_at  创建时间
 * @property string $updated_at  更新时间
 */
class Ad extends BaseModel
{
    protected $fillable = [
        'name',
        'category_id',
        'status',
        'image',
        'url',
        'target',
    ];
    // 指明这两个字段是日期类型
    protected $dates = ['before', 'after'];

    /**
     * 广告分类 一对多.
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(AdCategory::class);
    }
}
