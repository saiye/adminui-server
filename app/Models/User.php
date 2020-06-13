<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;

class User extends Authenticatable
{
    use Notifiable,ModelDataFormat;



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

    public function getLockAttribute($value)
    {
        return Config::get('deploy.lock.'.$value);
    }

    public function getJudgeAttribute($value)
    {
        return Config::get('deploy.judge.'.$value);
    }

}
