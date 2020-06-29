<?php

namespace App\Http\Controllers\Cp\Game;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Device;
use App\Models\PhysicsAddress;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class IndexController extends Controller
{

    public function userList()
    {
        $data = new User();
        if ($this->req->search_name) {
            $data = $data->where('account', 'like', '%' . $this->req->search_name . '%')
                ->orWhere('real_name', 'like', '%' . $this->req->search_name . '%')
                ->orWhere('nickname', 'like', '%' . $this->req->search_name . '%')
                ->orWhere('email', 'like', '%' . $this->req->search_name . '%');
        }
        $data = $data->orderBy('users.id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 添加用户
     */
    public function addUser()
    {
        $validator = Validator::make($this->req->all(), [
            'account' => ['regex:/^[0-9A-Za-z]+$/', 'required', 'max:20', 'min:3', 'unique:users,account'],
            'password' => 'required|max:100',
            'real_name' => 'max:20',
            'nickname' => 'required|max:20',
            'email' => ['max:20', 'unique:users,email', 'email'],
            'sex' => 'required|in:0,1,2',
            'judge' => 'required|in:1,2',
        ], [
            'account.required' => '会员账号，不能为空！',
            'account.max' => '会员账号最大长度20！',
            'account.min' => '会员账号最小长度3！',
            'account.regex' => '会员账号必须是字母数字的组合！',
            'account.unique' => '会员账号已存在，请用其他账号注册！',
            'password.required' => '会员密码，不能为空！',
            'real_name.max' => '真实姓名，不能大于20字符',
            'nickname.required' => '昵称，不能为空！',
            'email.max' => 'email，不能大于20字符！',
            'email.unique' => 'email，已经存在',
            'email.email' => '你输入的email，不正确!',
            'sex.required' => '性别必须选择',
            'sex.in' => '性别选择错误',
            'judge.required' => '是否为法官必须选择',
            'judge.in' => '是否为法官取值错误',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data = $this->req->except('created_at', 'updated_at', 'password', 'affirm_password', 'id');
        $data['password'] = Hash::make($this->req->input('password'));
        $user = User::create($data);
        if ($user) {
            return $this->successJson([], '添加成功');
        } else {
            return $this->errorJson('入库失败');
        }
    }


    public function editUser()
    {
        $validator = Validator::make($this->req->all(), [
            'password' => 'max:100',
            'real_name' => 'max:20',
            'nickname' => 'required|max:20',
            'email' => ['max:20', 'email'],
            'sex' => 'required|in:0,1,2',
            'judge' => 'required|in:1,2',
        ], [
            'real_name.max' => '真实姓名，不能大于20字符',
            'nickname.required' => '昵称，不能为空！',
            'email.max' => 'email，不能大于20字符！',
            'email.email' => '你输入的email，不正确!',
            'sex.required' => '性别必须选择',
            'sex.in' => '性别选择错误',
            'judge.required' => '是否为法官必须选择',
            'judge.in' => '是否为法官取值错误',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data = $this->req->except('created_at', 'updated_at', 'password', 'affirm_password', 'account');
        $password = $this->req->input('password');
        if ($password) {
            $data['password'] = Hash::make($password);
        }
        $user = User::whereId($this->req->id)->first();
        if (!$user) {
            return $this->errorJson('不存在用户!');
        }
        $hasEmail = User::where('id', '!=', $this->req->id)->whereEmail($this->req->email)->first();
        if ($hasEmail) {
            return $this->errorJson('该邮箱已占用!');
        }
        $save = $user->update($data);
        if ($save) {
            return $this->successJson([], '修改成功');
        } else {
            return $this->errorJson('修改失败');
        }
    }


    public function lockUser()
    {
        $validator = Validator::make($this->req->all(), [
            'user_id' => 'required|numeric',
            'lock' => 'required|numeric|in:1,2',

        ], [
            'user_id.required' => '商户id不能为空',
            'user_id.numeric' => '商户id是一个数字',
            'lock.required' => '审核状态必须',
            'lock.numeric' => '审核状态是一个数字',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $success = User::whereId($this->req->user_id)->update([
            'lock' => $this->req->lock,
        ]);
        if ($success) {
            return $this->successJson([], '操作成功');
        }
        return $this->errorJson('审核失败！');
    }
}

