<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
class NoteSms extends Model
{
    public   $timestamps=false;
    protected $appends = ['status_word','created_at'];

    protected $guarded = [
        'id'
    ];


    public function getStatusWordAttribute($value)
    {
        return Config::get('phone.status.'.$this->status,0);
    }


    public function getCreatedAtAttribute()
    {
        return $this->attributes['created_at'] = date('Y-m-d H:i:s',$this->create_time);
    }


    public function getMsgAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setMsgAttribute($value)
    {
        $this->attributes['msg'] = json_encode($value);
    }
    public function getResAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setResAttribute($value)
    {
        $this->attributes['res'] = json_encode($value);
    }

}
