<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    public   $timestamps=false;
    public $table='certificate';
    protected $guarded = [
        'id'
    ];
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

}
