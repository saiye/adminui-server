<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $table = 'api_log';
    public   $timestamps=false;
    protected $guarded = [
        'id'
    ];
}
