<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundOrder extends Model
{
    protected $table = 'refund_order';
    protected $guarded = [
        'id'
    ];

    public function order(){
        return $this->hasOne(Order::class,'order_id','order_id');
    }
}
