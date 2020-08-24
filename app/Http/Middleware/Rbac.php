<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Config;
use Route;
use App\TraitInterface\BaseTrait;

class Rbac
{
    use BaseTrait;
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = Auth::guard($guard)->user();
        if (!$user) {
            return $this->errorJson('你还没登录', 50008);
        }
        //登录后进行rbac权限判断
        if (!$this->rbac($request, $user, $guard)) {
            return $this->errorJson('你没有权限访问！');
        }
        return $next($request);
    }

    public function rbac($request, $user, $guard)
    {
        if ($user->lock) {
            return false;
        }
        //超级管理员，一般是开发者账号，直接跳过数据库权限判断
        $super_admin = Config::get('role.super_admin', []);
        if (in_array($user->user_name, $super_admin)) {
            return true;
        }
        //去掉$prefix
        $tmpPath=$request->path();

        $path =substr($tmpPath,strpos($tmpPath,'/')+1);
        //不需要rbac权限的路由,pass
        if ($this->checkAct($path,$guard)) {
            return true;
        }
        $acts = $user->acts->pluck('act')->toArray();
        //权限纪录，如果有权限，pass
        if (in_array(str_replace('/', '.', $path), $acts)) {
            return true;
        }
        return false;
    }

    public function checkAct($path, $guard)
    {
        $menu = Config::get($guard);
        $rbacAct = Config::get('role.rbac', ['login']);
        $data = [];
        foreach ($menu as $sub1) {
            foreach ($sub1['child'] as $sub2) {
                foreach ($sub2['child'] as $sub3) {
                    $data[$sub3['url']] = $sub3['act'];
                }
            }
        }
        if (array_key_exists($path, $data)) {
            if (in_array($data[$path], $rbacAct)) {
                return true;
            }
        }
        return false;
    }


}
