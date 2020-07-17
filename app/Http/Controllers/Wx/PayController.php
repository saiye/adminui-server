<?php

namespace App\Http\Controllers\Wx;


use App\Models\Channel;
use App\Models\Device;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use App\Service\LoginApi\LoginApi;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayController extends Base
{

    /**
     * 微信支付回调
     */
    public function callWx(){

    }

    /**
     * 支付宝回调
     */
    public function callAli(){

    }



}
