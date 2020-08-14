<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Order extends Model
{
    use ModelDataFormat;

    public $primaryKey = 'order_id';
    protected $table = 'order';
    protected $guarded = [
        'order_id', 'play_type', 'play_status'
    ];
    protected $appends = ['pay_date', 'pay_type_word', 'status_word', 'pay_status_word'];



    public function getPayDateAttribute()
    {
        return $this->attributes['pay_date'] = $this->pay_time ? date('Y-m-d H:i:s', $this->pay_time) : '-';
    }

    public function getPayTypeWordAttribute()
    {
        return $this->attributes['pay_type_word'] = Config::get('pay.pay_type.'.$this->pay_type,'-');
    }

    public function getStatusWordAttribute()
    {
        return $this->attributes['status_word'] = Config::get('pay.status.'.$this->status,'-');
    }

    public function getPayStatusWordAttribute()
    {
        return $this->attributes['pay_status_word'] =Config::get('pay.pay_state.'.$this->pay_status,'-');
    }

    public function orderGoods(){
        return $this->hasMany(OrderGoods::class,'order_id','order_id');
    }

    public function store(){
        return $this->hasOne(Store::class,'store_id','store_id');
    }
    public function company(){
        return $this->hasOne(Company::class,'company_id','company_id');
    }

    public function coupon()
    {
        return $this->hasOneThrough(GoodsCoupon::class, UserCoupon::class,'coupon_id','coupon_id','coupon_id','user_coupon_id');
    }

    public function userCoupon()
    {
        return $this->hasOne(UserCoupon::class,'user_coupon_id','coupon_id');
    }

}
