<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class PlayerGameLog extends Model
{
    use ModelDataFormat;
    public   $timestamps=false;
    public  $primaryKey='id';
    protected $table = 'player_game_log';
    protected $connection = 'log';

    protected $dates = ['begin_tick'];

    protected $guarded = [
        'id'
    ];

    public function  user(){
        return $this->hasOne(User::class,'id','user_id');
    }
    public function  board(){
        return $this->hasOne(GameBoard::class,'dup_id','dup_id');
    }

    public function roomGameLog(){
        return $this->hasOne(RoomGameLog::class,'id','room_game_id');
    }
}
