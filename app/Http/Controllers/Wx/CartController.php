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
     * 添加到购物车
     */
    public function addCart()
    {

    }

    /**
     * 清空购物车
     */
    public function clearCart()
    {

    }


}
