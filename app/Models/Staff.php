<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use Notifiable;
    public  $primaryKey='staff_id';
    protected $table = 'staff';

    protected $guarded = [
        'staff_id'
    ];

    protected $hidden = [
        'password',
    ];
}
