<?php

namespace App\Http\Middleware;

use App\Constants\ErrorCode;
use Closure;
use Auth;
use App\Events\ActionLogEvent;

class SignCheck
{
    public function handle($request, Closure $next)
    {
        $sign = $request->header('sign');
        $data = $request->input();
        $key = '123456';
        $tmpSign = makeSign($data, $key);
        if ($sign == $tmpSign) {
            return $next($request);
        }
        return response()->json([
            'errorMessage' => '签名错误!',
            'code' => ErrorCode::SIGN_CHECK_FAIL,
        ], 200);
    }
}
