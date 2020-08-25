<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteSms extends Model
{
    public   $timestamps=false;
    protected $guarded = [
        'id'
    ];

    public function getMsgAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setMsgAttribute($value)
    {
        $this->attributes['msg'] = json_encode($value);
    }

}
