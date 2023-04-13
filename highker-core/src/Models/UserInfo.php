<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Observers\UserInfoObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class UserInfo.
 *
 * @property int user_id
 * @property int province_id
 * @property int city_id
 * @property int district_id
 * @property int signs
 * @property int zodiac
 * @property int birthday
 * @property int description
 * @property int follow_count
 * @property int fans_count
 * @property int feed_count
 * @property int comment_count
 * @property int add_box_count
 * @property int get_box_count
 * @property int visit_count
 * @property int created_at
 * @property int updated_at
 */
class UserInfo extends BaseModel
{
    /**
     * 重定义主键.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    protected $appends = ['advent_days'];

    protected $hidden = ['user_id', 'created_at', 'updated_at'];

    protected $fillable = ['user_id', 'region', 'birthday', 'emotion', 'purpose', 'description'];

    protected static function boot()
    {
        parent::boot();

        static::observe(UserInfoObserver::class);
    }

    protected function adventDays(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => now()->diffInDays($attributes['created_at']),
        );
    }
}
