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
