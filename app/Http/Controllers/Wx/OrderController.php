<?php

namespace App\Http\Controllers\Wx;

use App\Constants\ErrorCode;
use App\Models\Order;
use App\Service\Order\HandelOrder;
use Illuminate\Support\Facades\Auth;

class OrderController extends Base
{

    /**
     * 下单接口
     */
    public function createOrder(HandelOrder $handelOrder)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'buys' => 'required|json',
            'user_coupon_id' => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $buys = json_decode($this->request->input('buys'), true);
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
            if (isset($data[$buy['type']])) {
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
        $order = Order::with('orderGoods')->whereOrderId($this->req->order_id)->first();
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
                'order' => $order
            ]
        );
    }

    /**
     * 发起支付
     */
    public function doPay()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'order_id' => 'required|numeric',
            'pay_type' => 'required|numeric|in,1,2',
        ], [
            'pay_type.in' => '该支付方式不支持！',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        //
        $orderId = $this->req->input('order_id');
        $order = Order::whereOrderId($orderId)->first();
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

        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
            'order_sn' => $order->order_sn,
        ]);
    }
}
