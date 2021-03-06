<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Http\Controllers\Cp\Main;

use App\Http\Controllers\Cp\BaseController;
use Illuminate\Support\Facades\Hash;
use Redirect;
use App\Models\CpUser;
use Auth;
use Route;
use Illuminate\Support\Str;
use Validator;

class HomeController extends BaseController
{

    /**
     * 首页
     */
    public function getHome()
    {
        return $this->successJson([], 'home', []);
    }

    /**
     * successJson
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postLogin()
    {
        $validator = Validator::make($this->req->all(), [
            'user_name' => 'required|max:255',
            'password' => 'required|max:255',
        ], [
            'user_name.required' => '用户名不能为空！',
            'password.required' => '密码不能为空！',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $user = CpUser::whereUserName($this->req->user_name)->first();
        if ($user) {
            if ($user->lock) {
                return $this->errorJson('用户名已被锁定，请联系管理员!', 2, []);
            }
            if (Hash::check($this->req->password, $user->password)) {
                $token = hash('sha256', Str::random(60));
                $data['last_ip'] = $user->current_ip;
                $data['current_ip'] = $this->req->ip();
                $data['current_login_at'] = date('Y-m-d H:i:s');
                $data['last_login_at'] = $user->current_login_at;
                $data['api_token'] = $token;
                CpUser::where('id', $user->id)->update($data);
                return $this->successJson([
                    'token' => $token,
                    'user_name' => $user->user_name,
                    'avatar' => $user->avatar,
                ], '登录成功');
            }
        }
        return $this->errorJson('用户名或密码错误!', 3, []);
    }

    public function getLogin()
    {
        return $this->errorJson('login view', 2, []);
        // return $this->view('layout.app');
    }

    public function getLogout()
    {
        $user = Auth::guard('cp')->user();
        if ($user) {
            $token = hash('sha256', Str::random(60));
            CpUser::whereId($user->id)->update([
                'api_token' => $token
            ]);
        }
        return $this->successJson([], '退出登录成功!');
    }

    public function getCantAccess()
    {
        return $this->errorJson('抱歉!你没有权限', 2, []);
    }

    public function getUserInfo()
    {
        $user = Auth::guard('cp')->user();
        if ($user) {
            return $this->successJson([
                'user_id' => $user->id,
                'user_name' => $user->user_name,
                'avatar' => $user->avatar,
                'email' => $user->email,
                'role_id' => $user->role_id,
            ]);
        }
        return $this->errorJson('抱歉!你没有登录，或登录过期！', 2, []);
    }

    public function getRoleMenu()
    {
        $act = request()->path();
        $user = Auth::guard('cp')->user();
        $menus = $user->roleMenu();
        return $this->successJson($menus);
    }

}
