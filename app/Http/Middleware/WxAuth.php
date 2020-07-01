<?php

namespace App\Http\Middleware;

use App\Constants\CacheKey;
use App\Constants\ErrorCode;
use Closure;
use Illuminate\Support\Facades\Cache;

class WxAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('token');
        $user = Cache::get($token);
        if ($user) {
            return $next($request);
        } else {
            return response()->json([
                'errorMessage' => '你未登录!',
                'code' => ErrorCode::ACCOUNT_NOT_LOGIN,
            ], 200);
        }

    }
}
