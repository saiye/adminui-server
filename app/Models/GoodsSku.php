<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsSku extends Model
{
    protected $table = 'goods_sku';
    protected $guarded = [
        'sku_id'
    ];
}
