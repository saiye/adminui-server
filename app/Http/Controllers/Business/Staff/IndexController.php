<?php

namespace App\Http\Controllers\Business\Staff;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Staff;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class IndexController extends Controller
{

    public function storeList()
    {
        $data = new Staff();
        if ($this->req->staff_id) {
            $data = $data->whereStaffId($this->req->staff_id);
        }
        if ($this->req->account) {
            $data = $data->where('account', 'like', '%' . $this->req->account . '%');
        }
        if ($this->req->real_name) {
            $data = $data->where('real_name', 'like', '%' . $this->req->real_name . '%');
        }
        if ($this->req->sex) {
            $data = $data->where('sex', $this->req->sex);
        }
        if ($this->req->phone) {
            $data = $data->where('phone', $this->req->phone);
        }
        if ($this->req->lock) {
            $data = $data->where('lock', $this->req->lock);
        }
        if ($this->req->type) {
            $data = $data->where('type', $this->req->type);
        }
        if ($this->req->company_id) {
            $data = $data->where('company_id', $this->req->company_id);
        }
        if ($this->req->store_id) {
            $data = $data->where('store_id', $this->req->store_id);
        }
        $limit=$this->req->input('limit',PaginateSet::LIMIT);
        $data = $data->orderBy('id', 'desc')->paginate($limit)->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 添加门店
     */
    public function addStaff()
    {
        $validator = Validator::make($this->req->all(), [
            'account' => 'required|max:30',
            'password' => 'required|max:100',
            'real_name' => 'required|max:20',
            'sex' => 'required|in:1,2,3',
            'phone' => 'required|max:11|number',
            'type' => 'required|in:1,2,3,4',
            'company_id' => 'required|number',
            'store_id' => 'required',
        ], [
            'account.required' => '账号是必须的!',
            'account.max' => '账号是最大长度30!',
            'password.required' => '密码是必须的!',
            'password.max' => '密码最大长度100!',
            'real_name.required' => '真实姓名是必须的!',
            'real_name.max' => '真实姓名最大长度30!',
            'sex.required' => '性别是必须的!',
            'phone.required' => '手机号码是必须的!',
            'phone.max' => '手机号码是11位的!',
            'phone.number' => '手机号码是数字!',
            'type.required' => '员工类型不能为空!',
            'type.in' => '员工类型错误!',
            'company_id.required' => '所属商户不能未空!',
            'company_id.number' => '所属商户值错误!',
            'store_id.number' => '所属门店错误!',
            'store_id.required' => '所属门店不能未空!',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }

        $data['account'] = $this->req->account;
        $data['password'] = $this->req->password;
        $data['real_name'] = $this->req->real_name;
        $data['sex'] = $this->req->sex;
        $data['phone'] = $this->req->phone;
        $data['type'] = $this->req->type;
        $data['company_id'] = $this->req->company_id;
        $data['store_id'] = $this->req->store_id;
        $staffObj = Staff::create($data);
        $staffObj->save();

        //联系人入库
        $staff = [
            'account' => $this->req->account,
            'real_name' => $this->req->real_name,
            'sex' => $this->req->sex,
            'phone' => $this->req->phone,
            'lock' => 1,
            'type' => 2,
            'company_id' => $this->req->company_id,
        ];
        Staff::create($staff)->save();

        return $this->successJson([], '操作成功');
    }

    /**
     * 审核门店
     */
    public function lockStaff()
    {
        $validator = Validator::make($this->req->all(), [
            'staff_id' => 'required|numeric',
            'lock' => 'required|numeric',

        ], [
            'staff_id.required' => '员工id不能为空',
            'staff_id.numeric' => '员工id是一个数字',
            'lock.required' => '锁定状态必须',
            'lock.numeric' => '锁定状态是一个数字',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $success = Staff::whereStaffId($this->req->staff_id)->update([
            'lock' => $this->req->req->lock,
        ]);
        if ($success) {
            return $this->successJson([], '操作成功');
        }
        return $this->errorJson('审核失败！');
    }


}

