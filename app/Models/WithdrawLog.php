<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawLog extends Model
{
    protected $table = 'withdraw_log';
    protected $guarded = [
        'id'
    ];
}
