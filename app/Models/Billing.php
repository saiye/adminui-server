<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Billing extends Model
{
    use ModelDataFormat;
    public  $primaryKey='billing_id';
    protected $table = 'billing';

    protected $guarded = [
        'billing_id'
    ];

    public function getPriceTypeAttribute($value)
    {
        return Config::get('deploy.price_type.'.$value.'.name');
    }

    public function getTimeTypeAttribute($value)
    {
        return Config::get('deploy.time_type.'.$value.'.name');
    }
}
