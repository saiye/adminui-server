<?php

namespace App\Http\Controllers\Wx;


use App\Models\Channel;
use App\Models\Device;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use App\Service\LoginApi\LoginApi;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartController extends Base
{
    /**
     * 下单预览
     */
    public function preview(){
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
    public function createOrder(){
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



}
