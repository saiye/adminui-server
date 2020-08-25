<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Store extends Model
{
    use ModelDataFormat;
    public $primaryKey = 'store_id';
    protected $table = 'store';

    protected $appends = ['check_word'];

    protected $guarded = [
        'store_id'
    ];

    public function staff()
    {
        return $this->hasOne('App\Models\Staff', 'staff_id', 'staff_id');
    }

    public function region()
    {
        return $this->hasOne('App\Models\Area', 'area_id','region_id');
    }

    public function province()
    {
        return $this->hasOne('App\Models\Area',  'area_id','province_id');
    }

    public function city()
    {
        return $this->hasOne('App\Models\Area', 'area_id','city_id');
    }

    public function company(){
        return $this->hasOne('App\Models\Company', 'company_id','company_id');
    }

    public function category(){
         return   $this->hasMany('App\Models\GoodsCategory','store_id','store_id');
    }

    public function tags(){
        return $this->hasMany(StoreTag::class,'store_id','store_id');
    }

    public function room(){
        return $this->hasMany(Room::class,'store_id','store_id');
    }

    public function image(){
        return $this->hasMany(Image::class,'foreign_id','store_id');
    }

    public function getCheckWordAttribute()
    {
        return $this->attributes['check_word'] = Config::get('deploy.check.'.$this->check);
    }


}
