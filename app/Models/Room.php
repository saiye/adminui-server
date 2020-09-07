<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Room extends Model
{
    use ModelDataFormat;

    public $primaryKey = 'room_id';
    protected $table = 'room';
    protected $guarded = [
        'room_id'
    ];

    protected $casts = [
       // 'store_id' => 'string',
       // 'company_id' => 'string',
       // 'room_id' => 'string',
    ];

    public function channel(){
        return $this->hasOne('App\Models\Channel', 'channel_id', 'channel_id');
    }

    public function store()
    {
        return $this->hasOne('App\Models\Store', 'store_id', 'store_id');
    }

    public function company()
    {
        return $this->hasOne('App\Models\Company', 'company_id', 'company_id');
    }

    public function billing()
    {
        return $this->hasOne('App\Models\Billing', 'billing_id', 'billing_id');
    }

    public function devices()
    {
        return $this->hasMany('App\Models\Device', 'room_id', 'room_id')->orderBy('seat_num','asc');
    }


    public function getIsUseAttribute($value)
    {
        return Config::get('deploy.is_use.'.$value,$value);
    }

}
