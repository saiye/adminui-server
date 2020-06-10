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
use App\Models\CpAct;
use App\Models\CpRole;
use App\Models\CpUser;
use Config;
use Hash;
use Auth;
use Illuminate\Validation\Rule;
use Validator;


class SysController extends BaseController
{

    public function getUserList(CpUser $user)
    {
        $data = $user->orderBy('id', 'desc');
        if ($this->req->id) {
            $data = $data->whereId($this->req->id);
        }
        if ($this->req->name) {
            $data = $data->where('name', 'like', '%' . $this->req->name . '%');
        }
        if ($this->req->role) {
            $data = $data->whereRole($this->req->role);
        }
        if ($this->req->email) {
            $data = $data->where('email', 'like', '%' . $this->req->email . '%');
        }
        $data = $data->paginate($this->req->input('limit',15))->appends($this->req->except('page'));

        foreach ($data as &$v) {
            $v->role_name = $v->cpRole->role_name;
            $v->lock_status = $v->lock();
        }
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function getLockUser(CpUser $user)
    {
        $user->whereId($this->req->user_id)->update(['lock' => $this->req->lock]);
        return $this->successJson([], '操作成功');
    }

    public function getAddUser(CpRole $cprole)
    {
        $roles = $cprole->get();
        $assign = compact('roles');
        return $this->successJson($assign, '操作成功');
    }

    public function postAddUser(CpUser $user)
    {
        $validator = Validator::make($this->req->all(), [
            'password' => 'required|max:191',
            'user_name' => 'required|max:20|unique:cp_users',
            'role_id' => 'required|integer',
            'email' => 'required|email|max:50',
        ], [
            'password.required' => '密码是必须！',
            'user_name.unique' => '用户已经存在！',
            'user_name.required' => '用户名不能为空！',
            'email.required' => '邮箱不能为空！',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('params error', 2, $validator->errors()->toArray());
        }
        $data['user_name'] = $this->req->user_name;
        $data['role_id'] = $this->req->role_id;
        $data['email'] = $this->req->email;
        $data['password'] = Hash::make($this->req->password);
        $data['last_ip'] = $this->req->ip();
        $data['current_ip'] = $this->req->ip();
        $data['current_login_at'] = date('Y-m-d H:i:s');
        $data['last_login_at'] = date('Y-m-d H:i:s');
        CpUser::insert($data);
        return $this->successJson([], '操作成功');
    }

    public function getEditUser(CpUser $user, CpRole $cprole)
    {
        $item = $user->find($this->req->id);
        if (!$item) {
            abort(404);
        }
        $roles = $cprole->all();
        $assign = compact('item', 'roles');
        return $this->successJson($assign, '获取成功');
    }

    public function postEditUser(CpUser $cpuser)
    {

        $validator = Validator::make($this->req->all(), [
            'password' => 'max:255',
            'user_name' => ['required', 'max:50', Rule::unique('cp_users')->ignore($this->req->user_id)],
            'role_id' => 'required|max:50',
            'email' => ['required', 'email', 'max:50'],
            'user_id' => 'required',
        ], [
            'user_name.required' => '用户名是必须的！',
            'role_id.required' => '角色id是必须的！',
            'user_name.required' => '用户名已经存在，用户名必须是唯一的！',
            'email.required' => '邮箱必填!',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误！', 2, $validator->errors()->toArray());
        }
        $data = $this->req->except('_token','user_id', 'password');
        if ($this->req->password)
            $data['password'] = Hash::make($this->req->password);
        $cpuser->where('id', $this->req->user_id)->update($data);
        return $this->successJson([], '修改成功');
    }

    public function getRoleList(CpRole $role)
    {
        $limit = $this->req->input('limit', 10);
        $data = $role->orderBy('role_id', 'desc')->paginate($limit)->appends($this->req->except('page'));
        $assgin = compact('data');
        return $this->successJson($assgin);
    }

    public function getDelRole(CpRole $role)
    {
        if ($this->req->role_id) {
            $role->find($this->req->role_id)->acts()->delete();
            $role->find($this->req->role_id)->delete();
            return $this->successJson([], '删除成功');
        }
        return $this->errorJson('角色id不能为空！');
    }


    public function postAddRole()
    {
        $validator = Validator::make($this->req->all(), [
            'role_name' => 'required|max:50|unique:cp_roles',
        ], [
            'role_name.required' => '角色名必须的！',
            'role_name.max' => '角色名最大长度是50！',
            'role_name.unique' => '角色名已经存在，角色名必须是唯一的！',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误！', 2, $validator->errors()->toArray());
        }
        CpRole::create($this->req->except('_token'));
        return $this->successJson([], '添加成功');
    }

    public function getEditRole(CpRole $role)
    {
        if ($this->req->role_id) {
            $item = $role->find($this->req->role_id);
            if (!$item) {
                abort(404);
            }
            $menu = Config::get('cp');
            //采集
            $data = $item->acts->pluck('act')->toArray();
            foreach ($menu as $k => &$v) {
                $v['checked'] = in_array($k, $data) ? true : false;
                foreach ($v['child'] as $i => &$sub) {
                    $sub['checked'] = in_array($k . '.' . $i, $data) ? true : false;
                    foreach ($sub['child'] as $j => &$m) {
                        $m['checked'] = in_array($k . '.' . $i . '.' . $j, $data) ? true : false;
                    }
                }
            }
            $assign = compact('item', 'menu');
            return $this->successJson($assign);
        }
        return $this->errorJson('角色id不能为空！');
    }

    public function postEditRole(CpAct $cpAct)
    {
        $this->validate($this->req, [
            'act' => 'required|max:255',
            'role_id' => 'required|integer',
        ]);
        $cpAct->whereRoleId($this->req->role_id)->delete();
        foreach ($this->req->act as $k => $v) {
            $cpAct->create(array(
                'role_id' => $this->req->role_id,
                'act' => $v,
            ));
        }
        return $this->successJson([], '修改成功！');
    }

}
