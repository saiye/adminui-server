<?php

namespace App\Service\Pay;

use App\Constants\ErrorCode;
use App\Models\User;
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
   public  function callBack($call)
    {
        return [
            'code' => ErrorCode::SUCCESS,
            'errorMessage' => '验证成功',
        ];
    }

    /*
     * 统一下单
     */
    public function createOrder($order)
    {
        //此处只管下单，不做余额是否充足的验证,另开发接口给前端发起余额支付，
        return [
            'code' => ErrorCode::SUCCESS,
            'errorMessage' => '下单',
        ];
    }

    /**
     * 退款
     * @param \Closure $call
     */
    public function  refundApply($refund_order,$call){

    }

    /**
     * 退款结果通知
     * @param \Closure $call
     */
    public function refundNotice($call){

    }

    /**
     * 主动查询订单
     * @param $order
     * @param \Closure $closure
     * @return bool|mixed|void
     */
    public function findOrder($order,\Closure $closure){
        return false;
    }
}
