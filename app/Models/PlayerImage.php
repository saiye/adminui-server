<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerImage extends Model
{
    public $timestamps = false;
    public $primaryKey = 'id';
    protected $table = 'player_images';
    protected $connection = 'log';
    protected $guarded = [
        'id'
    ];
}
