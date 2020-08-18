<?php
/**
 * Created by 2020/8/11 0011 21:30
 * User: yuansai chen
 */

namespace App\Service\Pay;

use App\Constants\ErrorCode;
use App\Models\Order;
use App\TraitInterface\ApiTrait;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;

class HandelPay
{
    use ApiTrait;

    private $app = null;

    /**
     * @var PayContracts
     */
    private $handel = null;


    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function make($pay_type)
    {
        switch ($pay_type) {
            case 1:
                $aliases = 'WeiXinPayApi';
                break;
            case 5:
                $aliases = 'DefaultPayApi';
                break;
            default:
                $aliases = 'DefaultPayApi';
        }
        $this->handel = $this->app->make($aliases);
        return $this;
    }

    public function createOrder($order)
    {
        return $this->json($this->handel->createOrder($order));
    }

    public function callBack()
    {
        return $this->handel->callBack(function ($data) {
            $order = Order::whereOrderSn($data['order_sn'])->first();
            if ($order) {
                $delta = 0.01;
                $status = $order->status == 3 ? 3 : 1;
                $is_abnormal=0;
                if ((abs($data['actual_payment'] - $order->due_price) < $delta) and (abs($data['total_price']-$order->total_price)<$delta)) {
                    $post=[
                        'pay_time' => strtotime($data['time_end']),
                        'pay_status' => 1,
                        'pay_type' => $data['pay_type'],
                        'prepay_id' => $data['prepay_id'],
                        'actual_payment' => $data['actual_payment'],
                        'is_abnormal' => 0,
                        'status' => $status,
                    ];
                } else {
                    $is_abnormal=1;
                    //订单异常：
                    $post=[
                        'actual_payment' => $data['actual_payment'],
                        'is_abnormal' => $is_abnormal,
                    ];
                }
                Order::whereOrderSn($data['order_sn'])->update($post);
                if($is_abnormal==0){
                    return true;
                }
                return false;
            }
            Log::info('wxCall验证ok,但订单不存在!');
            Log::info($data);
            return false;
        });
    }

    /**
     * 退款申请
     * @param $refund_order
     * @return mixed
     */
    public function refundApply($refund_order)
    {
        return $this->handel->refundApply($refund_order, function ($data) {

        });
    }

    /**
     * 主动查询订单
     * @param $data
     * @param \Closure $closure
     * @return mixed|void
     */
    public function findOrder($order)
    {
        return $this->handel->findOrder($order, function ($data) {
            $order = Order::whereOrderSn($data['order_sn'])->first();
            if ($order) {
                $delta = 0.01;
                $status = $order->status == 3 ? 3 : 1;
                $is_abnormal=0;
                if ((abs($data['actual_payment'] - $order->due_price) < $delta) and (abs($data['total_price']-$order->total_price)<$delta)) {
                    $post=[
                            'pay_time' => strtotime($data['time_end']),
                            'pay_status' => 1,
                            'pay_type' => $data['pay_type'],
                            'prepay_id' => $data['prepay_id'],
                            'actual_payment' => $data['actual_payment'],
                            'is_abnormal' => 0,
                            'status' => $status,
                    ];
                } else {
                    //订单异常：
                    $is_abnormal=1;
                    $post=[
                        'actual_payment' => $data['actual_payment'],
                        'is_abnormal' => $is_abnormal,
                    ];
                }
                Order::whereOrderSn($data['order_sn'])->update($post);
                if($is_abnormal==0){
                    return true;
                }
                return false;
            }
        });
    }

}
