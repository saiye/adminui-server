<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;

class GameBoard extends Model
{
    use ModelDataFormat;
    public $primaryKey = 'board_id';
    protected $table = 'game_board';
    protected $guarded = [
        'board_id'
    ];
    protected $casts = [

    ];
}
