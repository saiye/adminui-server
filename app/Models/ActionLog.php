<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    public   $timestamps=false;
    protected $guarded = [
        'id'
    ];
}
