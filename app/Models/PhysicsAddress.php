<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
class PhysicsAddress extends Model
{
    protected $table = 'physics_address';
    public   $timestamps=false;
    protected $guarded = [
        'id'
    ];

    protected $casts = [
       // 'id' => 'string',
    ];


}
