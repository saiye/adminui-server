<?php
/**
 * Created by 2020/8/11 0011 21:44
 * User: yuansai chen
 */

namespace App\Service\Pay;

interface PayContracts
{
    /**
     * 统一下单
     * @param $order
     * @return mixed
     */
    public function createOrder($order);

    /**
     * 支付回调
     * @return mixed
     */
    public function callBack(\Closure $closure);

}
