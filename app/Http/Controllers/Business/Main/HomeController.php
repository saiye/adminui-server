<?php

namespace App\Http\Controllers\Business\Main;

use App\Http\Controllers\Business\BaseController;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Redirect;
use Auth;
use Route;

use Illuminate\Support\Str;

class HomeController extends BaseController
{

    /**
     * 首页
     */
    public function getHome()
    {
        return $this->successJson([], 'business home', []);
    }

    /**
     * successJson
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postLogin()
    {
        $this->validate($this->req, [
            'account' => 'required|max:255',
            'password' => 'required|max:255',
        ]);
        $user = Staff::whereAccount($this->req->account)->first();
        if ($user) {
            if ($user->lock) {
                return $this->errorJson('用户名已被锁定，请联系管理员!', 2, []);
            }

            if (Hash::check($this->req->password,$user->password)) {
                $token = hash('sha256', Str::random(60));
                $data['last_ip'] = $user->current_ip;
                $data['current_ip'] = $this->req->ip();
                $data['current_login_at'] = date('Y-m-d H:i:s');
                $data['last_login_at'] = $user->current_login_at;
                $data['api_token'] = $token;
                Staff::whereStaffId( $user->staff_id)->update($data);
                return $this->successJson([
                    'token' =>$token,
                    'account' => $user->account,
                    'avatar' => '',
                ], '登录成功');
            }
        }
        return $this->errorJson('用户名或密码错误!', 3, []);
    }

    public function getLogout()
    {
        $user = Auth::guard('staff')->user();
        if ($user) {
            Auth::guard('staff')->logout();
        }
        return $this->successJson([], '退出登录成功!');
    }

    public function getCantAccess()
    {
        return $this->errorJson('抱歉!你没有权限', 2, []);
    }

    public function getUserInfo()
    {
        $user = Auth::guard('staff')->user();
        if ($user) {
            return $this->successJson([
                'staff_id' => $user->staff_id,
                'account' => $user->account,
                'phone' => $user->phone,
                'company_id' => $user->company_id,
            ]);
        }
        return $this->errorJson('抱歉!你没有登录，或登录过期！', 2, []);
    }

    public function getRoleMenu()
    {
        $act = request()->path();
        $user =Auth::guard('staff')->user();
        $menus = $user->roleMenu();
        return $this->successJson($menus);
    }

}
