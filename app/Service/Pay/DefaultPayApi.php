<?php

namespace App\Service\Pay;

use App\Constants\ErrorCode;
use Config;
use Request;
use Log;
use Illuminate\Support\Str;

/**
 * 钱包支付
 * @author chenyuansai
 *
 */
final class DefaultPayApi extends PayApi
{


    public function init()
    {
        return [];
    }

    /*
     * 微信支付回调
     */
    function callBack($call)
    {

    }

    /*
     * 统一下单
     */
    function createOrder($order)
    {

    }
}
