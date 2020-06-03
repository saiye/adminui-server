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

class CpRole extends Model
{
    public  $primaryKey='role_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id', 'role_name',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function acts()
    {
        return $this->hasMany('App\Models\CpAct','role_id','role_id');
    }
}
