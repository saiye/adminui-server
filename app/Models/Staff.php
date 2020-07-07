<?php
namespace App\Models;
use App\TraitInterface\ModelDataFormat;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff  extends Authenticatable
{
    use Notifiable,ModelDataFormat;
    use ModelDataFormat;
    public  $primaryKey='staff_id';
    protected $table = 'staff';

    protected $guarded = [
        'staff_id'
    ];

    protected $hidden = [
        'password','api_token',
    ];
}
