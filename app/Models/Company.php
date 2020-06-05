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
use Config;
class Company extends Model
{
    public  $primaryKey='company_id';
    public   $timestamps=false;
    protected $table = 'company';

    protected $guarded = [
        'company_id'
    ];
}
