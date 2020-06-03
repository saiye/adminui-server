<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;

class Filter
{
    protected $except = [
        '/main/setting/add',
        '/main/setting/edit',
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //过滤敏感词
        if(!empty($request->except('_token','id','page','password')) and !$this->inExceptArray($request)){
            $set= new Setting();
            $set=$set->checkRequest($request);
            if(!$set[0] ){
                if ($request->expectsJson()) {
                    return response()->json(['status' =>'failure','msg'=>'你的输入不能包含敏感词#'.$set[1].'#'],200);
                }
                return redirect()->back()->withErrors('你的输入不能包含敏感词#'.$set[1].'#');
            }
        }
        return $next($request);
    }

    public  function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            if ($request->is($except)) {
                return true;
            }
        }
        return false;
    }
}
