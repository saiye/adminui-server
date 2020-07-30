<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Goods extends Model
{
    use ModelDataFormat;

    protected $table = 'goods';
    public $primaryKey = 'goods_id';

    protected $guarded = [
        'goods_id'
    ];


    public function images()
    {
        return $this->hasMany(GoodsImage::class, 'goods_id', 'goods_id');
    }

    public function getImageAttribute($val)
    {
        if ($val) {
            return Storage::url($val);
        }
        return '';
    }

}
