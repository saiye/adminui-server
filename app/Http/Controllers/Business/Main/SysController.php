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
        $data = $user->where('staff.company_id','=',$this->loginUser->company_id);
        if(in_array($this->loginUser->role_id,[3,4])){
            $data=$data->where('staff.store_id',$this->loginUser->store_id);
        }
        if($this->req->search_name){
            $data = $data->where('staff.account', 'like', '%' . $this->req->search_name. '%')->orWhere('staff.real_name', 'like', '%' . $this->req->search_name. '%')->orWhere('store.store_name','like','%'.$this->req->search_name.'%');
        }
        if ($this->req->role_id) {
            $data = $data->whereRoleId($this->req->role_id);
        }
        $data = $data->select(['staff.staff_id','staff.real_name','staff.account','staff.role_id','staff.lock','staff.phone','staff.store_id','staff.created_at','store.store_name'])->leftJoin('store','staff.store_id','=','store.store_id')->orderBy('staff.staff_id', 'desc')->paginate($this->req->input('limit',PaginateSet::LIMIT))->appends($this->req->except('page'));


        $roleList=Config::get('business.role_list');

        foreach ($data as &$v) {
            $v->role_name =$roleList[$v->role_id]?$roleList[$v->role_id]['role_name']:$v->role_id;
        }
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function getLockUser(Staff $user)
    {
        $user->whereStaffId($this->req->staff_id)->update(['lock' => $this->req->lock]);
        return $this->successJson([], '操作成功');
    }

    public function postAddUser(Staff $user)
    {
        $validator = Validator::make($this->req->all(), [
            'password' => 'required|max:191',
            'account' =>['regex:/^[0-9A-Za-z]+$/', 'required', 'max:20','unique:staff,account'],
            'role_id' => 'required|numeric',
            'store_id' => 'required|numeric',
            'real_name' => 'required',
            'phone' => 'required',
        ], [
            'password.required' => '密码是必须！',
            'account.required' => '账号，不能为空！',
            'account.max' => '账号最大长度20！',
            'account.regex' => '账号必须是字母数字的组合！',
            'account.unique' => '账号已存在，请用其他账号注册！',
            'role_id.required' => '请选择角色！',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('params error', 2, $validator->errors()->toArray());
        }
        $data['account'] = $this->req->account;
        $data['company_id'] = $this->loginUser->company_id;
        $data['role_id'] = $this->req->role_id;
        $data['store_id'] = $this->req->store_id;
        $data['real_name'] = $this->req->real_name;
        $data['lock'] = 1;
        $data['phone'] = $this->req->phone;
        $data['password'] = Hash::make($this->req->password);
        $data['last_ip'] = $this->req->ip();
        $data['current_ip'] = $this->req->ip();
        $data['current_login_at'] = date('Y-m-d H:i:s');
        $data['last_login_at'] = date('Y-m-d H:i:s');
        Staff::create($data);
        return $this->successJson([], '操作成功');
    }

    public function postEditUser(Staff $Staff)
    {
        $validator = Validator::make($this->req->all(), [
            'password' => 'max:255',
            'account' => ['required', 'max:20','regex:/^[0-9A-Za-z]+$/', Rule::unique('staff')->ignore($this->req->staff_id,'staff_id')],
            'role_id' => 'required|numeric',
            'real_name' => 'required',
            'phone' => 'required',
            'store_id' => 'required',
            'staff_id' => 'required',
        ], [
            'account.regex' => '账号名取值只能是字母数字组合！',
            'account.required' => '账号名是必须的！',
            'account.unique' => '账号名已经存在，账号名必须是唯一的！',
            'role_id.required' => '角色是必须选择！',
            'store_id.required' => '门店必须选择！',
            'staff_id.required' => '店员id不能为空！',
            'real_name.required' => '员工名称必须填写！',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误！', 2, $validator->errors()->toArray());
        }
        $data = $this->req->except('_token','staff_id', 'password','role_name','store_name');
        if ($this->req->password)
            $data['password'] = Hash::make($this->req->password);
        $Staff->where('staff_id', $this->req->staff_id)->update($data);
        return $this->successJson([], '修改成功');
    }

    public function getRoleList()
    {
        $data =array_values(Config::get('business.role_list'));
        $assgin = compact('data');
        return $this->successJson($assgin);
    }

    public function getEditRole(StaffAct $acts)
    {
        if ($this->req->role_id) {
            $roleList =Config::get('business.role_list');
            $roleIds=array_keys($roleList);
            if (!in_array($this->req->role_id,$roleIds)) {
                return $this->errorJson('角色不存在！');
            }
            $menu = Config::get('staff');
            //采集
            $data = $acts->whereCompanyId($this->loginUser->company_id)->whereRoleId($this->req->role_id)->pluck('act')->toArray();
            foreach ($menu as $k => &$v) {
                $v['checked'] = in_array($k, $data) ? true : false;
                foreach ($v['child'] as $i => &$sub) {
                    $sub['checked'] = in_array($k . '.' . $i, $data) ? true : false;
                    foreach ($sub['child'] as $j => &$m) {
                        $m['checked'] = in_array($k . '.' . $i . '.' . $j, $data) ? true : false;
                    }
                }
            }
            $item=$roleList[$this->req->role_id];
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
        $companyId=$this->loginUser->company_id;
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
