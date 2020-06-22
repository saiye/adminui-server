<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
class Goods extends Model
{
    use ModelDataFormat;
    protected $table = 'goods';

    protected $guarded = [
        'goods_id'
    ];

}
