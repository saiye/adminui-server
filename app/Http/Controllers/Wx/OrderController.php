<?php

namespace App\Http\Controllers\Wx;

use App\Constants\ErrorCode;
use App\Models\Order;
use App\Models\UserCoupon;
use App\Service\Order\HandelOrder;
use App\Service\Pay\HandelPay;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Base
{


    /**
     * 下单接口
     */
    public function createOrder(HandelOrder $handelOrder)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'buys' => 'required|json',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $buys = json_decode($this->request->input('buys','{}'), true);
        $data = [];
        foreach ($buys as $buy) {
            $validator2 = $this->validationFactory->make($buy, [
                'goodsId' => 'required|numeric',
                'ext' => 'required|array',
                'type' => 'required|numeric|in:1,2',
                'count' => 'required|numeric|min:1',
            ]);
            if ($validator2->fails()) {
                return $this->json([
                    'errorMessage' => $validator2->errors()->first(),
                    'code' => ErrorCode::VALID_FAILURE,
                ]);
            }
            if (!isset($data[$buy['type']])) {
                $data[$buy['type']] = [];
            }
            //根据商品类型分组
            array_push($data[$buy['type']], $buy);
        }
        $user = Auth::guard('wx')->user();
        if ($user) {
            return $handelOrder->createOrder($data, $user);
        }
        return $this->json([
            'errorMessage' => '登录超时！',
            'code' => ErrorCode::ACCOUNT_NOT_LOGIN,
        ]);
    }

    /**
     * 订单列表
     */
    public function orderList(){

        $validator = $this->validationFactory->make($this->request->all(), [
            'page' => 'required|numeric|min:1',
            'limit' => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $limit = $this->request->input('limit', 10);
        $page = $this->request->input('page', 1);
        $skip = ceil($page - 1) * $limit;
        $user=$this->user();
        $list=Order::select(['order_id', 'store_id', 'actual_payment', 'total_price', 'integral_price','pay_status','pay_type','coupon_price','created_at','status'])->with(['store' => function ($q) {
            $q->select('store.store_id', 'store.store_name', 'store.logo', 'store.address', 'company.company_name')->leftJoin('company', 'store.company_id', 'company.company_id');
        },'orderGoods' => function ($r) {
            $r->select('order_id', 'goods_num', 'goods_name', 'image', 'tag', 'goods_price');
        },])->where('status','!=',2)->whereUserId($user->id)->orderBy('status','asc')->orderBy('order_id','desc')->skip($skip)->take($limit)->get();
        if ($list) {
            return $this->json(
                [
                    'errorMessage' => '',
                    'code' => ErrorCode::SUCCESS,
                    'list' => $list,
                ]
            );
        }
        return $this->json(
            [
                'errorMessage' => '没有更多数据了!',
                'code' => ErrorCode::DATA_NULL,
                'list' => [],
            ]
        );
    }

    /**
     * 订单详情
     */
    public function detail()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'order_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $order = Order::select(['order_id','order_sn', 'pay_type', 'store_id', 'actual_payment','pay_time', 'total_price', 'coupon_id','coupon_price','integral_price','created_at'])->with(['orderGoods' => function ($r) {
            $r->select('order_id', 'goods_num', 'goods_name', 'image', 'tag', 'goods_price');
        }, 'store' => function ($q) {
            $q->select('store.store_id', 'store.store_name', 'store.logo', 'store.address', 'company.company_name')->leftJoin('company', 'store.company_id', 'company.company_id');
        },'userCoupon'=>function($q){
            $q->select('coupon_name','condition_price','price');
        }])->whereOrderId($this->request->input('order_id'))->first();
        if (!$order) {
            return $this->json([
                'errorMessage' => '订单不存在！',
                'code' => ErrorCode::ORDER_NOT_FIND,
            ]);
        }
        return $this->json(
            [
                'errorMessage' => '',
                'code' => ErrorCode::SUCCESS,
                'order' => [
                    'order_id' => $order->order_id,
                    'order_sn' => $order->order_sn,
                    'pay_type' => $order->pay_type,
                    'pay_date' => $order->pay_date,
                    'pay_type_word' => $order->pay_type_word,
                    'total_price' => $order->total_price,
                    'actual_payment' => $order->actual_payment,
                    'integral_price' => $order->integral_price,
                    'coupon_price' => $order->coupon_price,
                    'integral' => $order->integral_price*100,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ],
                'user_coupon'=>$order->userCoupon,
                'order_goods' => $order->orderGoods,
                'store' => $order->store,
            ]
        );
    }

    /**
     * 订单预览,发起支付前一步，用于选择优惠券之类的。
     */
    public function preview()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'order_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $user = $this->user();
        $order = Order::select(['order_id', 'store_id', 'total_price','created_at'])->with(['orderGoods' => function ($r) {
            $r->select('order_id', 'goods_num', 'goods_name', 'image', 'tag', 'goods_price');
        }, 'store' => function ($q) {
            $q->select('store.store_id', 'store.store_name', 'store.logo', 'store.address', 'company.company_name')->leftJoin('company', 'store.company_id', 'company.company_id');
        }])->whereOrderId($this->request->input('order_id'))->first();
        if (!$order) {
            return $this->json([
                'errorMessage' => '订单不存在！',
                'code' => ErrorCode::ORDER_NOT_FIND,
            ]);
        }
        $user_coupon_list = UserCoupon::select(['coupon_name', 'user_coupon_id', 'condition_price', 'price'])->whereStoreId($order->store_id)->whereUserId($user->id)->whereIsUse(0)->get();
        return $this->json(
            [
                'errorMessage' => '',
                'code' => ErrorCode::SUCCESS,
                'order' => [
                    'order_id' => $order->order_id,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ],
                'order_goods' => $order->orderGoods,
                'store' => $order->store,
                'user_coupon_list' => $user_coupon_list
            ]
        );
    }

    /**
     * 发起支付
     */
    public function doPay(HandelPay $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'order_id' => 'required|numeric',
            'user_coupon_id' => 'nullable|numeric',
            'pay_type' => 'required|numeric|in:1,5',
        ], [
            'pay_type.in' => '该支付方式不支持！',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $user=$this->user();
        $orderId = $this->request->input('order_id');
        $order = Order::whereUserId($user->id)->whereOrderId($orderId)->first();
        if (!$order) {
            return $this->json([
                'errorMessage' => '订单不存在！',
                'code' => ErrorCode::DATA_NULL,
            ]);
        }
        if ($order->play_status == 1) {
            return $this->json([
                'errorMessage' => '订单已经支付成功，请勿重复操作！',
                'code' => ErrorCode::DATA_NULL,
            ]);
        }
        $order['openid']=$user->account;
        return $api->make($this->request->input('pay_type'))->createOrder($order);
    }

    /**
     * 取消订单
     */
    public function cancel(){
        $validator = $this->validationFactory->make($this->request->all(), [
            'order_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $orderId = $this->request->input('order_id');
        $order = Order::with('store')->whereOrderId($orderId)->first();
        if (!$order) {
            return $this->json([
                'errorMessage' => '订单不存在！',
                'code' => ErrorCode::DATA_NULL,
            ]);
        }
        if($order->status==2){
            return $this->json([
                'errorMessage' => '请勿重复操作！',
                'code' => ErrorCode::ORDER_IS_CANCEL,
            ]);
        }
        if($order->pay_status==1){
            return $this->json([
                'errorMessage' => '订单已支付无法取消！',
                'code' => ErrorCode::ORDER_IS_PAY,
            ]);
        }
        $order->status=2;
        $order->save();
        return $this->json([
            'errorMessage' => '订单取消成功',
            'code' => ErrorCode::SUCCESS,
        ]);
    }
}
