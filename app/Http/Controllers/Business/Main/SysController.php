<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Http\Controllers\Business\Main;

use App\Constants\PaginateSet;
use App\Http\Controllers\Business\BaseController;
use App\Models\CpAct;
use App\Models\CpRole;
use App\Models\Staff;
use App\Models\StaffAct;
use Config;
use Hash;
use Auth;
use Illuminate\Validation\Rule;
use Validator;


class SysController extends BaseController
{

    public function getUserList(Staff $user)
    {
        $data = $user->orderBy('staff_id', 'desc');
        if ($this->req->id) {
            $data = $data->whereId($this->req->id);
        }
        if ($this->req->name) {
            $data = $data->where('name', 'like', '%' . $this->req->name . '%');
        }
        if ($this->req->role_id) {
            $data = $data->whereRoleId($this->req->role_id);
        }
        $data = $data->paginate($this->req->input('limit',PaginateSet::LIMIT))->appends($this->req->except('page'));

        $roleList=Config::get('business.role_list');

        foreach ($data as &$v) {
            $v->role_name =$roleList[$v->role_id]?$roleList[$v->role_id]['role_name']:$v->role_id;
        }
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function getLockUser(Staff $user)
    {
        $user->whereId($this->req->user_id)->update(['lock' => $this->req->lock]);
        return $this->successJson([], '操作成功');
    }

    public function postAddUser(Staff $user)
    {
        $validator = Validator::make($this->req->all(), [
            'password' => 'required|max:191',
            'account' =>['regex:/^[0-9A-Za-z]+$/', 'required', 'max:20'],
            'role_id' => 'required|numeric',
            'real_name' => 'required',
            'phone' => 'required',
            'store_id' => 'required',
        ], [
            'password.required' => '密码是必须！',
            'account.regex' => '账号名取值只能是字母数字组合！',
            'account.required' => '账号名是必须的！',
            'account.unique' => '账号名已经存在，账号名必须是唯一的！',
            'role_id.required' => '请选择角色！',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('params error', 2, $validator->errors()->toArray());
        }
        $data['account'] = $this->req->account;
        $data['role_id'] = $this->req->role_id;
        $data['password'] = Hash::make($this->req->password);
        $data['last_ip'] = $this->req->ip();
        $data['current_ip'] = $this->req->ip();
        $data['current_login_at'] = date('Y-m-d H:i:s');
        $data['last_login_at'] = date('Y-m-d H:i:s');
        Staff::insert($data);
        return $this->successJson([], '操作成功');
    }

    public function postEditUser(Staff $Staff)
    {
        $validator = Validator::make($this->req->all(), [
            'password' => 'max:255',
            'account' => ['required', 'max:20','regex:/^[0-9A-Za-z]+$/', Rule::unique('staff')->ignore($this->req->staff_id)],
            'role_id' => 'required|numeric',
            'real_name' => 'required',
            'phone' => 'required',
            'store_id' => 'required',
        ], [
            'account.regex' => '账号名取值只能是字母数字组合！',
            'account.required' => '账号名是必须的！',
            'account.unique' => '账号名已经存在，账号名必须是唯一的！',
            'role_id.required' => '角色是必须选择！',
            'store_id.required' => '门店必须选择！',
            'real_name.required' => '员工名称必须填写！',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误！', 2, $validator->errors()->toArray());
        }
        $data = $this->req->except('_token','user_id', 'password');
        if ($this->req->password)
            $data['password'] = Hash::make($this->req->password);
        $Staff->where('id', $this->req->user_id)->update($data);
        return $this->successJson([], '修改成功');
    }

    public function getRoleList()
    {
        $data =Config::get('business.role_list');
        $assgin = compact('data');
        return $this->successJson($assgin);
    }

    public function getEditRole(StaffAct $acts)
    {
        if ($this->req->type) {
            $roleList =Config::get('company.role_list');
            $roleIds=array_map(function ($role){
                return $role['role_id'];
            },$roleList);
            if (!in_array($roleIds,$this->req->type)) {
                return $this->errorJson('角色不存在！');
            }
            $menu = Config::get('business');
            //采集
            $data = $acts->pluck('act')->toArray();
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

    public function postEditRole(StaffAct $staffAct)
    {
        $this->validate($this->req, [
            'act' => 'required|max:255',
            'role_id' => 'required|integer',
        ]);
        $user=Auth::guard('staff')->user();
        $companyId=$user->company_id;
        $staffAct->whereRoleId($this->req->role_id)->whereCompanyId($companyId)->delete();
        foreach ($this->req->act as $k => $v) {
            $staffAct->create(array(
                'role_id' => $this->req->role_id,
                'act' => $v,
                'company_id' => $companyId,
            ));
        }
        return $this->successJson([], '修改成功！');
    }

}
