<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class WithdrawLog extends Model
{
    use ModelDataFormat;
    protected $table = 'withdraw_log';
    protected $appends = ['pay_type_word', 'check_status_word'];
    protected $guarded = [
        'id'
    ];

    public function getPayTypeWordAttribute()
    {
        return $this->attributes['pay_type_word'] = Config::get('pay.pay_type.' . $this->pay_type, '-');
    }

    public function getCheckStatusWordAttribute()
    {
        return $this->attributes['check_status_word'] = Config::get('pay.check_status.' . $this->check_status, '-');
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'company_id', 'company_id');
    }


}
