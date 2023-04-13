<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Exception;
use HighKer\Core\Enum\VipProduct;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    id
 * @property int    user_id
 * @property string no
 * @property int    vip_slug
 * @property string description
 * @property int    amount
 * @property int    closed
 * @property string payment_no
 * @property string payment_at
 * @property string created_at
 * @property string updated_at
 */
class VipOrder extends BaseModel
{
    protected $fillable = ['user_id', 'no', 'amount', 'description', 'vip_slug', 'type', 'remark'];

    public static function boot()
    {
        parent::boot();

        static::creating(function (VipOrder $vipOrder) {
            if (!$vipOrder->no) {
                $vipOrder->no = VipOrder::getAvailableNo();
            }
        });
    }

    /**
     * @throws Exception
     */
    public static function getAvailableNo()
    {
        do {
            $no = date('YmdHis').str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::query()->where('no', $no)->exists());

        return $no;
    }

    public static function create($userId, $type, $vipSlug, $remark = '')
    {
        $product = VipProduct::MAP[$vipSlug];

        return VipOrder::query()->create([
            'user_id'     => $userId,
            'type'        => $type,
            'vip_slug'    => $vipSlug,
            'amount'      => $product['price'],
            'description' => $product['name'],
            'remark'      => $remark,
        ]);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(VipOrder::class, 'user_id', 'user_id');
    }
}
