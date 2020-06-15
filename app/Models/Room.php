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
        return $this->hasMany('App\Models\Device', 'room_id', 'room_id');
    }

    public function getIsUseAttribute($value)
    {
        return Config::get('deploy.is_use.'.$value,$value);
    }

}
