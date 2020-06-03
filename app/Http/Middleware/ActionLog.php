<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Events\ActionLogEvent;
class ActionLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$guard)
    {
        //记录操作日志
        $user=Auth::guard($guard)->user();
        if($user){
            //event(new ActionLogEvent($user,$request,$guard));
        }
        return $next($request);
    }
}
