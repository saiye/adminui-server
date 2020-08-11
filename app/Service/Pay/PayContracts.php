<?php
/**
 * Created by 2020/8/11 0011 21:44
 * User: yuansai chen
 */

namespace App\Service\Pay;
use Illuminate\Http\Request;

interface PayContracts
{
    /**
     * 统一下单
     * @param $data
     * @return mixed
     */
    public function createOrder($data);

    /**
     * 支付回调
     * @return mixed
     */
    public function callBack(\Closure $closure);

}
