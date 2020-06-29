<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
class Device extends Model
{
    protected $table = 'device';
    public   $timestamps=false;
    protected $guarded = [
        'id'
    ];

    protected $casts = [
      //  'id' => 'string',
     //   'company_id' => 'string',
       // 'room_id' => 'string',
      //  'store_id' => 'string',
    ];


    public function room(){
        return $this->hasOne('App\Models\Room', 'room_id','room_id');
    }

    public function store(){
        return $this->hasOne('App\Models\Store', 'store_id','store_id');
    }

    public function company(){
        return $this->hasOne('App\Models\Company', 'company_id','company_id');
    }


}
