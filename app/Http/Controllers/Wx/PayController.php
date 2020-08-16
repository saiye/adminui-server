<?php

namespace App\Http\Controllers\Wx;


use App\Constants\ErrorCode;
use App\Models\BalanceWater;
use App\Models\Order;
use App\Service\Pay\HandelPay;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayController extends Base
{

    /**
     * 微信支付回调
     */
    public function callWx(HandelPay $api)
    {
        return $api->make(1)->callBack();
    }


    /**
     * 余额支付回调
     */
    public function callYe(HandelPay $api)
    {
        return $api->make(5)->callBack();
    }

    /**
     * 余额支付
     */
    public function doPayYe()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'balance_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $user = Auth::guard('wx')->user();
        $balance_id = $this->request->input('balance_id');
        $balanceOrder = BalanceWater::whereUserId($user->id)->whereId($balance_id)->first();
        if (!$balanceOrder) {
            return $this->json([
                'errorMessage' => '订单不存在!',
                'code' => ErrorCode::DATA_NULL,
            ]);
        }
        if ($balanceOrder->status == 1) {
            return $this->json([
                'errorMessage' => '支付成功！',
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        $order = Order::whereUserId($user->id)->whereOrderId($balanceOrder->order_id)->first();
        if (!$order) {
            return $this->json([
                'errorMessage' => '订单不存在!',
                'code' => ErrorCode::DATA_NULL,
            ]);
        }
        if ($order->pay_status == 1) {
            return $this->json([
                'errorMessage' => '该订单已支付成功,请勿重复操作！',
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        if ($user->remaining >= $balanceOrder->price) {
            DB::beginTransaction();
            $queen_remaining = $user->remaining - $balanceOrder->price;
            $balanceOrder->status = 1;
            $saveLog = $balanceOrder->save();
            $user->remaining = $queen_remaining;
            $saveUser = $user->save();
            if ($saveLog and $saveUser) {
                DB::commit();
                //触发余额支付，回调队列
                return $this->json([
                    'errorMessage' => '支付成功！',
                    'code' => ErrorCode::SUCCESS,
                ]);
            } else {
                DB::rollBack();
                return $this->json([
                    'errorMessage' => '支付失败！',
                    'code' => ErrorCode::DATA_NULL,
                ]);
            }
        }
        return $this->json([
            'errorMessage' => '你的余额不足！',
            'code' => ErrorCode::BALANCE_CANT,
        ]);
    }


}
