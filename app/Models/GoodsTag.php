<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsTag extends Model
{
    protected $table = 'goods_tag';
    public  $primaryKey='tag_id';
    protected $guarded = [
        'tag_id'
    ];
}
