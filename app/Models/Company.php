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
    public $primaryKey = 'company_id';
    protected $table = 'company';
    //protected $dateFormat = 'Y-m-d H:i:s';
    protected $guarded = [
        'company_id'
    ];

    public function staffs()
    {
        return $this->hasMany('App\Models\Staff', 'company_id', 'company_id');
    }

    public function manage()
    {
        return $this->hasOne('App\Models\Staff', 'staff_id', 'staff_id');
    }
    public function getCheckAttribute($value)
    {
        return Config::get('deploy.check.'.$value);
    }

    public function state(){
        return  Config::get('deploy.state.'.$this->state_id);
    }
}
