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

class ThreeUser extends Model
{
    protected $table = 'three_users';
    protected $guarded = [
        'id'
    ];

}
