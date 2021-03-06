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

    public function getScoreAttribute($value)
    {
        return $value/10;
    }

    public function getReplayContentJsonAttribute($value){
        return json_decode($value, true);
    }
}
