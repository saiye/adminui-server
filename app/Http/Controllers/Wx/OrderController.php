<?php

namespace App\Http\Controllers\Wx;

use App\Constants\ErrorCode;

class OrderController extends Base
{

    /**
     * 订单预览
     */
    public function preview()
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
        $buys=json_decode($this->request->input('buys'),true);
        foreach ($buys as $buy){
            $validator2 = $this->validationFactory->make($buy, [
                'goodsId' => 'required|numeric',
                'skuIds' => 'required|array',
                'type' => 'required|numeric|in:1',
                'count' => 'required|numeric|min:1',
            ]);
            if ($validator2->fails()) {
                return $this->json([
                    'errorMessage' => $validator2->errors()->first(),
                    'code' => ErrorCode::VALID_FAILURE,
                ]);
            }
        }
    }


    /**
     * 下单接口
     */
    public function createOrder()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'store_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
    }



    /**
     * 发起支付
     */
    public function doPay()
    {

    }
}
