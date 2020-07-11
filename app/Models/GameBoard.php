<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;

class GameBoard extends Model
{
    protected $connection = 'mysql';
    public $primaryKey = 'board_id';
    protected $table = 'game_board';
    protected $guarded = [
        'board_id'
    ];
    protected $casts = [

    ];
}
