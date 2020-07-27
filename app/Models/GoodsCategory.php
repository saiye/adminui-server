<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsCategory extends Model
{
    protected $table = 'goods_category';
    protected $guarded = [
        'category_id'
    ];
    //移动到的分类id
    protected $appends = ['move_cat_id'];



    public function getMoveCatIdAttribute()
    {
        return $this->category_id;
    }
}
