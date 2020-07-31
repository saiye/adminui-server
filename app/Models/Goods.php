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

    protected $appends = ['img100', 'img50'];

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
}
