<?php

namespace App\Service\Pay;

use App\Constants\ErrorCode;
use Config;
use Request;
use Log;
use Illuminate\Support\Str;

/**
 * 微信支付
 * @author chenyuansai
 *
 */
final class DefaultPayApi extends PayApi
{


    public function init()
    {

    }

    /*
     * 微信支付回调
     */
    function callBack($call)
    {

    }

    /*
     * 微信统一下单
     */
    function createOrder($order)
    {

    }

}
