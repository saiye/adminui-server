<?php
namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
class Order extends Model
{
    use ModelDataFormat;
    public  $primaryKey='billing_id';
    protected $table = 'order';
    protected $guarded = [
        'order_id','play_type','play_status'
    ];

}
