<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class RoomGameLog extends Model
{
    public   $timestamps=false;
    public  $primaryKey='id';
    protected $table = 'room_game_log';
    protected $connection = 'log';
    protected $guarded = [
        'id'
    ];
}
