<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsCoupon extends Model
{
    protected $table = 'goods_coupon';
    protected $guarded = [
        'coupon_id'
    ];
}
