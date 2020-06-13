<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use ModelDataFormat;
    public   $timestamps=false;
    protected $guarded = [
        'id'
    ];
    protected $hidden = [

    ];

}
