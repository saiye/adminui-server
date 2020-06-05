<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Staff extends Model
{
    public  $primaryKey='staff_id';
    public   $timestamps=false;
    protected $table = 'staff';

    protected $guarded = [
        'company_id'
    ];
}
