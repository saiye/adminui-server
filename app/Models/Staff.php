<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Staff extends Model
{
    public  $primaryKey='staff_id';
    protected $table = 'staff';

    protected $guarded = [
        'staff_id'
    ];

    protected $hidden = [
        'password','api_token',
    ];
}
