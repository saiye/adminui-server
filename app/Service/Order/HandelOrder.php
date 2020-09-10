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
use App\Constants\Logic;
use \Illuminate\Contracts\Foundation\Application;

class HandelOrder
{
    use ApiTrait;

    public $app=null;

    public function __construct(Application $app)
    {
        $this->app=$app;
    }
    public function createOrder($data, $user)
    {
        $goodsArr = [];
        foreach ($data as $type => $buys) {
            switch ($type) {
                case Logic::BOOK_GOODS_TYPE:
                    $class = 'CheckGoodsOrder';
                    break;
                case Logic::BOOK_ROOM_TYPE:
                    $class = 'CheckRoomOrder';
                    break;
                default:
                    $class = 'DefaultCheckOrder';
                    break;
            }
            $obj=$this->app->make($class);
            list($status, $message, $list) = $obj->checkBuys($buys);
            if ($status!==0) {
                return $this->json([
                    'errorMessage' => $message,
                    'code' =>$status,
                    'removeGoodsIds' =>$list,
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
        $orderCount=count($ordersArr);
        if($orderCount==0){
            return $this->json([
                'errorMessage' => '商品不存在！',
                'code' => ErrorCode::GOODS_NOT_FIND,
            ]);
        }
        if ($orderCount>1) {
            return $this->json([
                'errorMessage' => '暂不允许跨店铺购物！',
                'code' => ErrorCode::STORE_ORDER_FAILURE,
            ]);
        }
        $order_id = 0;
        DB::beginTransaction();
        foreach ($ordersArr as $item) {
            $order = Order::create($item['order']);
            if ($order) {
                foreach ($item['order_goods'] as &$goods){
                    $goods['order_id']=$order->order_id;
                }
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
                'info' => $item[0]['info'],
                'order_sn' => date('YmdHis') . mt_rand(999, 99999),
                'total_price' => $totalPrice,
                'actual_payment' =>0,
                'due_price' => $totalPrice,
                'user_id' => $user->id,
                'nickname' => $user->nickname,
                'phone' => $user->phone??'',
                'room_id' => 0,
                'store_id' => $store_id,
                'company_id' => $item[0]['company_id'],
                'staff_id' => 0,
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
