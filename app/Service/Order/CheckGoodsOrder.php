<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/10
 * Time: 10:24
 */

namespace App\Service\Order;


use App\Models\Goods;

class CheckGoodsOrder implements CheckOrder
{
    public function checkBuys($buys)
    {
        $goodsIds = [];
        foreach ($buys as $buy) {
            array_push($goodsIds, $buy['goodsId']);
        }
        /**
         * 跨门店订单处理
         */
        $goodsList = Goods::select('goods_name', 'goods_id', 'store_id', 'stock', 'company_id', 'image')->whereIn('goods.goods_id', $goodsIds)->get();
        $goodsList = Goods::tagList($goodsList);
        $date = date('Y-m-d H:i:s');
        $data = [];
        foreach ($goodsList as $goods) {

            foreach ($buys as $buy) {
                if ($buy['goodsId'] == $goods['goods_id']) {
                    array_push($data, [
                        'info' => $goods->goods_name,
                        'store_id' => $goods->store_id,
                        'company_id' => $goods->company_id,
                        'staff_id' => 0,
                        'order_goods' => [
                            'goods_id' => $goods->goods_id,
                            'goods_num' => $buy['count'],
                            'goods_price' => 0,
                            'type' => 1,
                            'image' => '',
                            'tag' => '',
                            'goods_name' => $goods->goods_name,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ],
                    ]);
                }
            }

        }
        if ($data) {
            return [true, 'success', $data];
        }
        return [false, '商品不存在！', []];
    }
}
