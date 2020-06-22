<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
class OrderGoods extends Model
{
    use ModelDataFormat;
    protected $table = 'order_goods';

    protected $guarded = [
        'order_goods_id'
    ];


}
