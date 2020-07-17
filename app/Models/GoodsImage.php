<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsImage extends Model
{
    protected $table = 'goods_image';
    protected $guarded = [
        'goods_image_id'
    ];
}
