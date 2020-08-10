<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/10
 * Time: 10:30
 */

namespace App\Service\Order;


use App\Constants\ErrorCode;
use App\Models\Order;
use App\Models\OrderGoods;
use App\TraitInterface\ApiTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HandelOrder
{
    use ApiTrait;

    const BOOK_GOODS = 1;
    const BOOK_ROOM = 2;

    public function createOrder($data, $user)
    {
        $goodsArr = [];
        foreach ($data as $type => $buys) {
            switch ($type) {
                case self::BOOK_GOODS:
                    $class = '\\App\\Service\\Order\\CheckGoodsOrder';
                    break;
                case self::BOOK_ROOM:
                    $class = '\\App\\Service\\Order\\CheckRoomOrder';
                    break;
                default:
                    $class = '\\App\\Service\\Order\\DefaultCheckOrder';
                    break;
            }
            $obj = new $class();
            list($status, $message, $list) = $obj->checkBuys($buys);
            if (!$status) {
                return $this->json([
                    'errorMessage' => $message,
                    'code' => ErrorCode::VALID_FAILURE,
                ]);
            } else {
                //
                foreach ($list as $item) {
                    if (!isset($goodsArr[$item['store_id']])) {
                        $goodsArr[$item['store_id']] = [];
                    }
                    array_push($goodsArr[$item['store_id']], $item);
                }
            }
        }
        $ordersArr = $this->orderCompute($goodsArr, $user);
        if (count($ordersArr) !== 1) {
            return $this->json([
                'errorMessage' => '暂不允许跨店铺购物！',
                'code' => ErrorCode::STORE_ORDER_FAILURE,
            ]);
        }
        $order_id = 0;
        DB::beginTransaction();
        foreach ($ordersArr as $item) {
            $order = Order::created($item['order']);
            if ($order) {
                $saveOrderGoods = OrderGoods::insert($item['order_goods']);
                if (!$saveOrderGoods) {
                    DB::rollBack();
                    return $this->json([
                        'errorMessage' => '订单商品入库失败！',
                        'code' => ErrorCode::ORDER_GOODS_FAILURE,
                    ]);
                }
                $order_id = $order->order_id;
            }
        }
        DB::commit();
        return $this->json([
            'errorMessage' => '订单创建成功',
            'code' => ErrorCode::SUCCESS,
            'order_id' => $order_id,
        ]);
    }

    /**
     * 订单计算,优惠券减额,积分减额,等相关计算
     */
    public function orderCompute($goodsArr, $user)
    {
        $ordersArr = [];
        foreach ($goodsArr as $store_id => $item) {
            $totalPrice = 0;
            foreach ($item as $goods) {
                $totalPrice += $goods['order_goods']['goods_price']*$goods['order_goods']['goods_num'];
            }
            $order = [
                'info' => $item['info'],
                'order_sn' => date('YmdHis') . mt_rand(999, 99999),
                'total_price' => $totalPrice,
                'user_id' => $user->user_id,
                'nickname' => $user->nickname,
                'phone' => $user->phone,
                'room_id' => 0,
                'store_id' => $item['store_id'],
                'company_id' => $item['company_id'],
                'staff_id' => $item['staff_id'],
                'pay_time' => 0,
                'coupon_id' => 0,
                'coupon_price' => 0,
            ];
            $orderGoods=[];
            foreach ($item as $goods){
                array_push($orderGoods,$goods['order_goods']);
            }
            array_push($ordersArr, [
                'order' => $order,
                'order_goods' =>$orderGoods,
            ]);
        }
        return $ordersArr;
    }
}
