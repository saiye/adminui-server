<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class ReceiptAccount extends Model
{
    use ModelDataFormat;
    protected $table = 'receipt_account';
    protected $appends = ['pay_type_word'];
    protected $guarded = [
        'id'
    ];
    public function getPayTypeWordAttribute()
    {
        return $this->attributes['pay_type_word'] = Config::get('pay.pay_type.' . $this->pay_type, '-');
    }

}
