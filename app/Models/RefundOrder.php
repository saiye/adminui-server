<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Facades\Config;

class RefundOrder extends Model
{
    protected $table = 'refund_order';

    protected $appends = ['check_status_word', 'refund_status_word','pay_type_word'];
    protected $guarded = [
        'id'
    ];

    public function order()
    {
        return $this->hasOne(Order::class, 'order_id', 'order_id');
    }

    public function getCheckStatusWordAttribute()
    {
        return $this->attributes['check_status_word'] = Config::get('pay.refund_check_status.' . $this->check_status, '-');
    }

    public function getRefundStatusWordAttribute()
    {
        return $this->attributes['refund_status_word'] = Config::get('pay.refund_status.' . $this->refund_status, '-');
    }

    public function getPayTypeWordAttribute()
    {
        return $this->attributes['pay_type_word'] = Config::get('pay.pay_type.'.$this->pay_type,'-');
    }

    public function user(){
        return $this->hasOne(User::class,'user_id','user_id');
    }




}
