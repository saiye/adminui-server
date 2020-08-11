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
                //微信
                $aliases = 'WeiXinPayApi';
                break;
            case 2:
                //余额支付
                $aliases = 'DefaultPayApi';
                break;
        }
        $this->handel = $this->app->make($aliases);

        return $this;
    }

    public function createOrder($data)
    {
        list($status,$message,$info)= $this->handel->createOrder($data);
        if ($status){
            return $this->json([
                'errorMessage' => '下单成功！',
                'code' => ErrorCode::SUCCESS,
                'info'=>$info,
            ]);
        }
        return $this->json([
            'errorMessage' => $message,
            'code' => ErrorCode::THREE_FAIL,
        ]);
    }

    public function callBack()
    {
        return $this->handel->callBack(function ($data){
            //回调成功相关逻辑
            $savePayStatus= Order::whereOrderSn($data['order_sn'])->update([
                'pay_status'=>1,
            ]);
        });
    }



}
