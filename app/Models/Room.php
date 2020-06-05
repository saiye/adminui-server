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
class Room extends Model
{
    public  $primaryKey='room_id';
    public   $timestamps=false;
    protected $table = 'room';

    protected $guarded = [
        'room_id'
    ];

}
