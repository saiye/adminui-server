<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class PlayerCountRecord extends Model
{
    public   $timestamps=false;
    public $primaryKey = 'user_id';
    protected $table = 'player_count_record';
    protected $connection = 'log';
    protected $fillable = ['user_id', 'total_game', 'win_game', 'mvp', 'svp', 'police'];
}
