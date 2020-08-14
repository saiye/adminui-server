<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundOrder extends Model
{
    protected $table = 'refund_order';
    protected $guarded = [
        'id'
    ];
}
