<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/10
 * Time: 10:24
 */

namespace App\Service\Order;


class DefaultCheckOrder implements CheckOrder
{
    public function checkBuys($buys)
    {
        return [false, '暂不支持该类型商品', []];
    }
}
