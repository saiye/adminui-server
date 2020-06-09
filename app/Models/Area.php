<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    public $primaryKey = 'store_id';
    public $timestamps = false;
    protected $table = 'area';
    protected $guarded = [
        'area_id'
    ];

}
