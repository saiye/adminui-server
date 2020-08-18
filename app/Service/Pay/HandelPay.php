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
                if (abs($data['actual_payment'] - $order->actual_payment) < $delta) {
                    $status = $order->status == 3 ? 3 : 1;
                    Order::whereOrderSn($data['order_sn'])->update([
                        'pay_time' => time(),
                        'pay_status' => 1,
                        'pay_type' => $data['pay_type'],
                        'prepay_id' => $data['prepay_id'],
                        'status' => $status,
                    ]);
                    return true;
                } else {
                    //订单异常：
                    Log::info('CallBackAbnormalOrders:');
                    Log::info($data);
                    return false;
                }
            }
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
                if (abs($data['actual_payment'] - $order->actual_payment) < $delta) {
                    if ($order->pay_status == 0) {
                        $status = $order->status == 3 ? 3 : 1;
                        Order::whereOrderSn($data['order_sn'])->update([
                            'pay_time' => time(),
                            'pay_status' => 1,
                            'pay_type' => $data['pay_type'],
                            'prepay_id' => $data['prepay_id'],
                            'status' => $status,
                        ]);
                    }
                } else {
                    //订单异常：
                    Log::info('findOrderAbnormalOrders:');
                    Log::info($data);
                }
            }
        });
    }

}
