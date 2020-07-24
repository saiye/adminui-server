<?php

namespace App\Service\Pay;

use App\Constants\ErrorCode;
use Illuminate\Http\Request;
use App\Models\Order;
use Log;

abstract class PayApi
{

    protected $order;

    protected $req;

    private function __construct()
    {

    }

    public static function make(Order $order, Request $req)
    {
        switch ($order->pay_type) {
            case 1:
                //微信
                $object_name = '\\App\\Service\\Pay\\WeiXinPayApi';
                break;
            case 2:
                //余额支付
                $object_name = '\\App\\Service\\Pay\\DefaultPayApi';
                break;
        }
        $obj = new $object_name;
        $obj->order = $order;
        $obj->req = $req;
        return $obj;
    }

    /**
     * 给前端返回对应的数据格式
     */
    public function json($data, $status = 200)
    {
        return response()->json($data, $status);
    }

    /**
     * 统一下单接口,对前端调用者返回对应的数据格式
     */
    abstract function createOrder();

    /**
     * 回调验证,并且按各支付渠道返回其需要的数据格式
     */
    abstract function callBack();
    /**
     * 验证ok回调
     * @return mixed
     * [
     * 'prepay_id'=>'第三方orderid',
     * 'callPrice'=>'第三方回调金额'
     * ]
     */
    public function success($data)
    {

    }
}
