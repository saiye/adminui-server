<?php

namespace App\Service\Pay;

use App\Constants\ErrorCode;
use App\Models\BalanceWater;
use Request;
use Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

/**
 * 钱包支付
 * @author chenyuansai
 *
 */
final class DefaultPayApi extends PayApi
{


    public function init()
    {
        $this->config = Config::get('pay.key.default');
    }

    /*
     * 支付回调，目前不做回调处理
     */
    public function callBack($call)
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
        $price = $order->due_price;
        $balance_sn = date('YmdHis') . mt_rand(11111, 99999);

        $createOrder=  BalanceWater::create([
            'balance_sn' => $balance_sn,
            'order_sn' => $order->order_sn,
            'price' => $price,
            'type' => 0,
            'status' => 0,
            'user_id' => $order->user_id,
        ]);
        if($createOrder){
            $sendData = [
                'appId' =>1,
                'timeStamp' => time(),
                'nonceStr' => Str::random(32),
                'balance_sn' => $balance_sn,
                'signType' => 'MD5',
            ];
            $sendData['sign'] = makeSign($sendData,$this->config['appSecret']);
            return [
                'code' => ErrorCode::SUCCESS,
                'errorMessage' => '下单',
                'data' =>$sendData,
            ];
        }
        return [
            'code' => ErrorCode::CREATE_ORDER_FAILURE,
            'errorMessage' => '下单失败',
        ];
    }

    /**
     * 退款
     * @param \Closure $call
     */
    public function refundApply($refund_order, $call)
    {

    }

    /**
     * 退款结果通知
     * @param \Closure $call
     */
    public function refundNotice($call)
    {

    }
    /**
     * 主动查询订单
     * @param $order
     * @param \Closure $closure
     * @return bool|mixed|void
     */
    public function findOrder($order, \Closure $closure)
    {
        return false;
    }
}
