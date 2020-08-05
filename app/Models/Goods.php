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
    protected $appends = ['img100', 'img50'];

    public function images()
    {
        return $this->hasMany(GoodsImage::class, 'goods_id', 'goods_id');
    }

    public function getImageAttribute($value)
    {
        if ($value) {
            return Storage::url($value);
        }
        return WebConfig::getKeyByFile('goods.image','');
    }

    public function getImg100Attribute()
    {
        if ($this->image) {
            return $this->attributes['img100'] = $this->image . '?x-oss-process=image/resize,m_lfit,h_100,w_100/format,png';
        }
        return '';
    }

    public function getImg50Attribute()
    {
        if ($this->image) {
            return $this->attributes['img50'] = $this->image . '?x-oss-process=image/resize,m_pad,h_50,w_50';
        }
        return '';
    }

    public function cat(){
        return $this->hasOne(GoodsCategory::class,'category_id','category_id');
    }


    public function sku(){
        return $this->hasMany(GoodsSku::class,'goods_id','goods_id');
    }

    public function tag(){
        return $this->hasManyThrough(GoodsTag::class, GoodsSku::class,'tag_id','tag_id');
    }

}
