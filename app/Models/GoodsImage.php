<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GoodsImage extends Model
{
    protected $table = 'goods_image';
    public $primaryKey = 'goods_image_id';
    protected $guarded = [
        'goods_image_id'
    ];

    protected $appends = ['img100', 'img50','img'];

    public function getImg100Attribute()
    {
        if ($this->image) {
            return $this->attributes['img100'] = Storage::url($this->image) . '?x-oss-process=image/resize,m_lfit,h_100,w_100/format,png';
        }
        return '';
    }
    public function getImgAttribute()
    {
        if ($this->image) {
            return $this->attributes['img100'] = Storage::url($this->image);
        }
        return '';
    }

    public function getImg50Attribute()
    {
        if ($this->image) {
            return $this->attributes['img50'] = Storage::url($this->image). '?x-oss-process=image/resize,m_pad,h_50,w_50';
        }
        return '';
    }

}
