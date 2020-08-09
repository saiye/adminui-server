<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreTag extends Model
{
    protected $table = 'store_tag';
    public   $timestamps=false;
    protected $guarded = [
        'id'
    ];
}
