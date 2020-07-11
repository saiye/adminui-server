<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;

class User extends Authenticatable
{
    //如果垮库链表需要定义这个
    protected $connection = 'mysql';

    use Notifiable, ModelDataFormat;

    protected $guarded = [
        'id'
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
        return $v ? $v : '';
    }
}
