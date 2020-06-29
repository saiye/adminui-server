<?php

namespace App\Http\Controllers\Cp\Company;

use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Area;
use App\Models\Company;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Constants\PaginateSet;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class IndexController extends Controller
{

    public function companyList()
    {
        $data = new Company();
        $data = $data->whereIn('check', $this->req->input('check', [1]))->with('manage');
        if ($this->req->company_id) {
            $data = $data->whereCompanyId($this->req->company_id);
        }
        if ($this->req->company_name) {
            $data = $data->where('company_name', 'like','%'.trim($this->req->company_name).'%');
        }
        if ($this->req->state_id) {
            $data = $data->whereStateId($this->req->state_id);
        }
        if ($this->req->status) {
            $data = $data->whereStatus($this->req->status);
        }
        if($this->req->listDate){
            $data = $data->whereBetween('created_at',$this->req->listDate);
        }
        if($this->req->real_name){
            $data = $data->where('staff.real_name','like','%'.$this->req->real_name.'%')->leftJoin('staff', 'company.staff_id', '=', 'staff.staff_id');
        }
        $data = $data->orderBy('company.company_id', 'desc')->paginate($this->req->input('limit',PaginateSet::LIMIT))->appends($this->req->except('page'));
        foreach ($data as &$v) {
            $v->state = $v->state();
        }
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 添加商户
     */
    public function addCompany()
    {
        $validator = Validator::make($this->req->all(), [
            'company_name' => ['required', 'max:30', 'unique:company,company_name'],
            'state_id' => 'required|integer',
            // 'has_license' => 'required',
            'account' => ['regex:/^[0-9A-Za-z]+$/', 'required', 'max:20', 'unique:staff,account'],
            'password' => 'required|max:100',
            'real_name' => 'required|max:100',
            'sex' => 'required|in:1,2',
            'phone' => ['required', 'regex:/^1[3|4|5|6|7|8|9][0-9]{9}$/', 'unique:staff,phone'],
            'proportion' => 'required|integer|min:1|max:100',
        ], [
            'company_name.required' => '商户名称不能为空！',
            'company_name.unique' => '商户名称已存在！',
            'state_id.required' => '商户所在国家不能为空！',
            'state_id.integer' => '商户所在国家参数必须是一个整数！',
            'has_license.required' => '营业执照，不能为空！',
            'account.required' => '账号，不能为空！',
            'account.max' => '商户账号最大长度20！',
            'account.regex' => '商户账号必须是字母数字的组合！',
            'account.unique' => '商户账号已存在，请用其他账号注册！',
            'password.required' => '商户密码，不能为空！',
            'real_name.required' => '联系人，不能为空！',
            'sex.required' => '性别必须选择',
            'sex.in' => '性别选择错误',
            'phone.required' => '手机号码，不能为空！',
            'phone.regex' => '你输入的不是手机号码',
            'phone.unique' => '你输入的手机号码已存在!',
            'proportion.required' => '分成比例不能为空！',
            'proportion.min' => '分成比例不能小于1！',
            'proportion.max' => '分成比例不能大于于100！',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        DB::beginTransaction();
        //商家入库
        $data['company_name'] = $this->req->company_name;
        $data['state_id'] = $this->req->state_id;
        $data['proportion'] = $this->req->proportion;
        $company = Company::create($data);

        //联系人入库
        $staff = [
            'account' => $this->req->account,
            'real_name' => $this->req->real_name,
            'sex' => $this->req->sex,
            'phone' => $this->req->phone,
            'lock' => 1,
            'type' => 1,
            'company_id' => $company->company_id,
            'password' => Hash::make($this->req->password),
        ];
        $staffObj = Staff::create($staff);
        //更新商户表
        $company->staff_id = $staffObj->staff_id;
        $isCompany = $company->save();

        if ($company and $staffObj and  $isCompany ) {
            DB::commit();
            return $this->successJson([], '操作成功');
        } else {
            DB::rollBack();
            return $this->errorJson('入库失败');
        }

    }

    /**
     * 审核商家
     */
    public function checkCompany()
    {
        $validator = Validator::make($this->req->all(), [
            'company_id' => 'required|numeric',
            'check' => 'required|numeric',
            'reason' => 'max:100',
        ], [
            'company_id.required' => '商户id不能为空',
            'company_id.numeric' => '商户id是一个数字',
            'check.required' => '审核状态必须',
            'check.numeric' => '审核状态是一个数字',
            'reason.max' => '拒绝原因不能超过100字',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $success = Company::whereCompanyId($this->req->company_id)->update([
            'check' => $this->req->check,
            'status'=>$this->req->check,
            'reason'=>$this->req->input('reason','.'),
        ]);
        if ($success) {
            return $this->successJson([], '操作成功');
        }
        return $this->errorJson('审核失败！');
    }


    /**
     * 禁封商家
     */
    public function lockCompany()
    {
        $validator = Validator::make($this->req->all(), [
            'company_id' => 'required|numeric',
            'status' => 'required|numeric|in:1,2',
        ], [
            'company_id.required' => '商户id不能为空',
            'company_id.numeric' => '商户id是一个数字',
            'status.required' => '禁封状态必须',
            'status.numeric' => '禁封状态是一个数字',
            'status.in' => '禁封状态取值有误!',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $success = Company::whereCompanyId($this->req->company_id)->update([
            'status'=>$this->req->status
        ]);
        if ($success) {
            return $this->successJson([], '操作成功');
        }
        return $this->errorJson('操作失败！');
    }

    public function getState()
    {
        $data = Config::get('deploy.state');
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function areaList()
    {
        $validator = Validator::make($this->req->all(), [
            'parent_id' => 'required|numeric',
        ], [
            'parent_id.required' => 'parent_id必须',
            'parent_id.numeric' => 'parent_id一个数字',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $res = Area::whereParentId($this->req->parent_id)->get();
        $data = [];
        foreach ($res as $val) {
            array_push($data, [
                'value' => $val->area_id,
                'label' => $val->area_name,
                'parent_id' => $val->parent_id,
                'level' => $val->level,
                'leaf' => $val->level == 3
            ]);
        }
        $assign = compact('data');
        return $this->successJson($assign);
    }
}

