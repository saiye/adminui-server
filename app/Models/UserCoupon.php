<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
    protected $table = 'user_coupon';
    protected $guarded = [
        'user_coupon_id'
    ];
}
