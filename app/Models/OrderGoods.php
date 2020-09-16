<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrderGoods extends Model
{
    use ModelDataFormat;
    protected $table = 'order_goods';

    protected $appends = ['img100', 'img50','refund_num','active'];

    protected $guarded = [
        'id'
    ];


    protected $casts = [
         'active' => 'boolean',
    ];

    public function getSkuArrAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setSkuArrAttribute($value)
    {
        $this->attributes['sku_arr'] = json_encode($value);
    }

    public function getRefundNumAttribute()
    {
        return $this->attributes['refund_num']='';
    }
    public function getActiveAttribute()
    {
        return $this->attributes['active']=false;
    }

    public function getImageAttribute($value)
    {
        if ($value) {
            return Storage::url($value);
        }
        return WebConfig::getKeyByFile('goods.image', '');
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
