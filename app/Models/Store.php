<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use ModelDataFormat;
    public $primaryKey = 'store_id';
    protected $table = 'store';
    protected $guarded = [
        'store_id'
    ];

    protected $casts = [
       // 'store_id' => 'string',
       // 'company_id' => 'string',
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
