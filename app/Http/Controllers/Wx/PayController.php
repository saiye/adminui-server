<?php

namespace App\Http\Controllers\Wx;


use App\Constants\ErrorCode;
use App\Models\BalanceWater;
use App\Models\Order;
use App\Service\Pay\HandelPay;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

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
     * 余额支付,不做回调处理.
     */
    public function balancePay()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'appId' => 'required|in:1',
            'timeStamp' => 'required|numeric',
            'nonceStr' => 'required',
            'balance_sn' => 'required',
            'signType' => 'required|in:MD5',
            'sign' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $data = $this->request->input();
        $sign = $this->request->input('sign');
        $balance_sn = $this->request->input('balance_sn');
        $key = Config::get('pay.key.default.appSecret');
        if (!checkSign($data, $sign, $key)) {
            return $this->json([
                'errorMessage' => '支付凭证效验失败!',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $user = Auth::guard('wx')->user();
        if(!$user){
            return $this->json([
                'errorMessage' => '你未登录!',
                'code' => ErrorCode::ACCOUNT_NOT_LOGIN,
            ]);
        }
        $balanceOrder = BalanceWater::whereBalanceSn($balance_sn)->first();
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
        $order = Order::whereUserId($user->id)->whereOrderSn($balanceOrder->order_sn)->first();
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
            $order->fill([
                'pay_time' => time(),
                'pay_status' => 1,
                'prepay_id' => $balance_sn,
                'actual_payment' => $balanceOrder->price,
                'is_abnormal' => 0,
                'status' => 1,
            ]);
            $orderSave = $order->save();
            if ($saveLog and $saveUser and $orderSave) {
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
