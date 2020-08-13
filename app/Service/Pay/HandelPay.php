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
            //支付成功相关逻辑
            $savePayStatus = Order::whereOrderSn($data['order_sn'])->update([
                'pay_status' => 1,
                'prepay_id' => $data['prepay_id'],
                'status' => 1,
            ]);
        });
    }


}
