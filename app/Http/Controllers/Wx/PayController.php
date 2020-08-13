<?php

namespace App\Http\Controllers\Wx;


use App\Service\Pay\HandelPay;

class PayController extends Base
{

    /**
     * 微信支付回调
     */
    public function callWx(HandelPay $api)
    {
        return $api->make(1)->callBack();
    }

    /**
     * 余额支付回调
     */
    public function callBalance()
    {

    }
}
