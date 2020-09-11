<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    //如果垮库链表需要定义这个
    protected $connection = 'mysql';

    use Notifiable, ModelDataFormat;


    protected $guarded = [
        'id'
    ];

    protected $casts = [
         'area_code' => 'string',
         'phone' => 'string',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function getIconAttribute($v)
    {
        if(!$v or $v=="''"){
            if($this->sex==0){
                $v=WebConfig::getKeyByFile('icon.boy','');
            }else{
                $v=WebConfig::getKeyByFile('icon.girl','');
            }
        }
        $arr=explode(':',$v);
        if(isset($arr[0]) and in_array($arr[0],['http','https'])){
            return $v;
        }
        return  Storage::url($v);
    }

    public function getBigIconAttribute($v)
    {
        if(!$v or $v=="''"){
            if($this->sex==0){
                $v=WebConfig::getKeyByFile('icon.boy','');
            }else{
                $v=WebConfig::getKeyByFile('icon.girl','');
            }
        }
        $arr=explode(':',$v);
        if(isset($arr[0]) and in_array($arr[0],['http','https'])){
            return $v;
        }
        return  Storage::url($v);
    }


}
