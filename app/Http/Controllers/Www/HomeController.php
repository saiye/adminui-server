<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/7/3
 * Time: 17:08
 */

namespace App\Http\Controllers\Www;


use App\Constants\ErrorCode;

class HomeController extends BaseController
{

    public function home()
    {
        return response()->json(['message' =>'你未登录', 'code' =>ErrorCode::ACCOUNT_NOT_LOGIN], 200);
    }

}
