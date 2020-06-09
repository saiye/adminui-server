<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    public $primaryKey = 'store_id';
    protected $table = 'store';
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
}
