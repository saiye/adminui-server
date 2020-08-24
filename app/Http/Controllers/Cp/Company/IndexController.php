<?php

namespace App\Http\Controllers\Cp\Company;

use App\Constants\CacheKey;
use App\Constants\SmsAction;
use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Area;
use App\Models\Company;
use App\Models\Image;
use App\Models\Staff;
use App\Service\SmsApi\HandelSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $data = $data->with(['manage', 'image' => function ($r) {
            $r->whereType(1)->whereIsDel(0);
        }])->whereIn('check', $this->req->input('check', [1]))->with('manage');
        if ($this->req->company_id) {
            $data = $data->whereCompanyId($this->req->company_id);
        }
        if ($this->req->company_name) {
            $data = $data->where('company_name', 'like', '%' . trim($this->req->company_name) . '%');
        }
        if ($this->req->area_code) {
            $data = $data->whereAreaCode($this->req->area_code);
        }
        if ($this->req->status) {
            $data = $data->whereStatus($this->req->status);
        }
        if ($this->req->listDate) {
            if ($this->req->listDate[0])
                $data = $data->whereBetween('created_at', $this->req->listDate);
        }
        if ($this->req->real_name) {
            $data = $data->where('staff.real_name', 'like', '%' . $this->req->real_name . '%')->leftJoin('staff', 'company.staff_id', '=', 'staff.staff_id');
        }
        $data = $data->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 添加商户
     */
    public function addCompany(HandelSms $api)
    {
        $validator = Validator::make($this->req->all(), [
            'company_name' => ['required', 'max:30', 'unique:company,company_name'],
            'area_code' => 'required|integer',
            'imageData' => 'required|array',
            'account' => ['regex:/^[0-9A-Za-z]+$/', 'required', 'max:20', 'unique:staff,account'],
            'password' => 'required|max:100',
            'real_name' => 'required|max:100',
            'sex' => 'required|in:1,2',
            'phone' => ['required'],
            'phone_area_code' => ['required'],
            'proportion' => 'required|integer|min:1|max:100',
        ], [
            'imageData.required' => '商户营业执照不能为空！',
            'imageData.array' => '商户营业执照参数格式错误！',
            'company_name.required' => '商户名称不能为空！',
            'company_name.unique' => '商户名称已存在！',
            'area_code.required' => '商户所在国家不能为空！',
            'area_code.integer' => '商户所在国家参数必须是一个整数！',
            'account.required' => '账号，不能为空！',
            'account.max' => '商户账号最大长度20！',
            'account.regex' => '商户账号必须是字母数字的组合！',
            'account.unique' => '商户账号已存在，请用其他账号注册！',
            'password.required' => '商户密码，不能为空！',
            'real_name.required' => '联系人，不能为空！',
            'sex.required' => '性别必须选择',
            'sex.in' => '性别选择错误',
            'phone.required' => '手机号码，不能为空！',
            'phone_area_code.required' => '手机区号，不能为空！',
            'phone.unique' => '你输入的手机号码已存在!',
            'proportion.required' => '分成比例不能为空！',
            'proportion.min' => '分成比例不能小于1！',
            'proportion.max' => '分成比例不能大于于100！',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $phone = $this->req->input('phone');
        $area_code = $this->req->input('area_code');
        $phone_area_code = $this->req->input('phone_area_code');

        $res = $api->phoneCheck($phone_area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->errorJson($res['errorMessage'], 2);
        }
        DB::beginTransaction();
        //商家入库
        $data['company_name'] = $this->req->company_name;
        $data['area_code'] = $area_code;
        $data['proportion'] = $this->req->proportion;
        $company = Company::create($data);

        //联系人入库
        $staff = [
            'account' => $this->req->account,
            'real_name' => $this->req->real_name,
            'sex' => $this->req->sex,
            'area_code' => $phone_area_code,
            'phone' => $phone,
            'lock' => 2,
            'role_id' => 1,
            'company_id' => $company->company_id,
            'password' => Hash::make($this->req->password),
        ];
        $staffObj = Staff::create($staff);
        //更新商户表
        $company->staff_id = $staffObj->staff_id;
        $isCompany = $company->save();
        $imagedata = $this->req->input('imageData', []);
        if ($imagedata) {
            //图片是你上传的,才关联
            $user = Auth::guard('cp')->user();
            $key = CacheKey::CP_UPLOAD_KEY . $user->id;
            $imageJson = Cache::get($key, '');
            if ($imageJson) {
                $tmp = json_decode($imageJson, true);
                $tmp = array_uintersect($tmp, $imagedata, "strcasecmp");
                if ($tmp) {
                    Image::whereIn('id', $tmp)->update([
                        'foreign_id' => $company->company_id
                    ]);
                } else {
                    return $this->errorJson('营业执照入库失败！');
                }
            }
        }
        if ($company and $staffObj and $isCompany) {
            DB::commit();
            return $this->successJson([], '操作成功');
        } else {
            DB::rollBack();
            return $this->errorJson('入库失败');
        }

    }


    /**
     * 编辑商户
     */
    public function editCompany(HandelSms $api)
    {
        $validator = Validator::make($this->req->all(), [
            'company_name' => ['required', 'max:30'],
            'company_id' => ['required', 'numeric'],
            'area_code' => 'required|integer',
            'imageData' => 'required|array',
            'account' => ['regex:/^[0-9A-Za-z]+$/', 'required', 'max:20'],
            'password' => 'nullable|max:100|min:6',
            'real_name' => 'required|max:100',
            'sex' => 'required|in:1,2',
            'phone' => ['required'],
            'phone_area_code' => ['required'],
            'proportion' => 'required|integer|min:1|max:100',
        ], [
            'imageData.required' => '商户营业执照不能为空！',
            'imageData.array' => '商户营业执照参数格式错误！',
            'company_name.required' => '商户名称不能为空！',
            'company_name.unique' => '商户名称已存在！',
            'area_code.required' => '商户所在国家不能为空！',
            'area_code.integer' => '商户所在国家参数必须是一个整数！',
            'account.required' => '账号，不能为空！',
            'account.max' => '商户账号最大长度20！',
            'account.regex' => '商户账号必须是字母数字的组合！',
            'account.unique' => '商户账号已存在，请用其他账号注册！',
            'password.required' => '商户密码，不能为空！',
            'real_name.required' => '联系人，不能为空！',
            'sex.required' => '性别必须选择',
            'sex.in' => '性别选择错误',
            'phone.required' => '手机号码，不能为空！',
            'phone_area_code.required' => '手机区号，不能为空！',
            'proportion.required' => '分成比例不能为空！',
            'proportion.min' => '分成比例不能小于1！',
            'proportion.max' => '分成比例不能大于于100！',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $phone = $this->req->input('phone');
        $area_code = $this->req->input('area_code');
        $phone_area_code = $this->req->input('phone_area_code');
        $res = $api->phoneCheck($phone_area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->errorJson($res['errorMessage'], 2);
        }

        $company_id = $this->req->input('company_id');
        $account = $this->req->input('account');
        $password = $this->req->input('password');
        $imagedata = $this->req->input('imageData', []);
        DB::beginTransaction();
        //商家入库
        $data['company_name'] = $this->req->company_name;
        $data['area_code'] = $area_code;
        $data['proportion'] = $this->req->proportion;
        $company = Company::whereCompanyId($company_id)->first();
        if (!$company) {
            return $this->errorJson('商户不存在！');
        }
        $company->fill($data);
        $company->save();

        $staff = Staff::whereAccount($account)->first();

        if ($staff) {
            if ($staff->company_id !== $company_id) {
                return $this->errorJson('商户账号冲突！');
            }
            //修改
            $staffData = [
                'real_name' => $this->req->real_name,
                'sex' => $this->req->sex,
                'phone' => $phone,
                'area_code' => $phone_area_code,
                'lock' => 2,
                'role_id' => 1,
                'company_id' => $company->company_id,
            ];
            if ($password) {
                $staffData['password'] = Hash::make($password);
            }
            $staff->fill($staffData);
            $staffObj = $staff->save();
        } else {
            //新增
            if (!$password) {
                return $this->errorJson('密码不能为空！');
            }
            $staffData = [
                'account' => $this->req->account,
                'real_name' => $this->req->real_name,
                'sex' => $this->req->sex,
                'phone' => $phone,
                'area_code' => $phone_area_code,
                'lock' => 2,
                'role_id' => 1,
                'company_id' => $company->company_id,
                'password' => Hash::make($password),
            ];
            $staffObj = Staff::create($staffData);
            //更新商户表
            $company->staff_id = $staffObj->staff_id;
            $company->save();
        }

        if ($imagedata) {
            //图片是你上传的,才关联
            $user = Auth::guard('cp')->user();
            $key = CacheKey::CP_UPLOAD_KEY . $user->id;
            $imageJson = Cache::get($key, '');
            if ($imageJson) {
                $tmp = json_decode($imageJson, true);
                $tmp = array_uintersect($tmp, $imagedata, "strcasecmp");
                if ($tmp) {
                    Image::whereIn('id', $tmp)->update([
                        'foreign_id' => $company->company_id
                    ]);
                }
            }
        }
        if ($company and $staffObj) {
            DB::commit();
            return $this->successJson([], '修改成功');
        } else {
            DB::rollBack();
            return $this->errorJson('入库失败');
        }
    }

    /**
     * 审核商家
     */
    public function checkCompany(HandelSms $api)
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
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $company = Company::whereCompanyId($this->req->input('company_id'))->first();
        $reason = $this->req->input('reason', '');
        if (!$company) {
            return $this->errorJson('商户不存在！');
        }
        $check = $this->req->input('check');
        $success = Company::whereCompanyId($this->req->company_id)->update([
            'check' => $check,
            'status' => $check,
            'reason' => $reason,
        ]);
        $checkStaff = Staff::whereCompanyId($this->req->company_id)->update([
            'lock' => 1,
        ]);
        if ($success and $checkStaff) {
            $staff = Staff::whereCompanyId($this->req->company_id)->whereStaffId($company->staff_id)->first();
            if ($check == 1) {
                $action = SmsAction::COMPANY_CHECK_SUCCESS;
                $tmpCode = 'company_check_success';
            } else {
                $action = SmsAction::COMPANY_CHECK_FAIL;
                $tmpCode = 'company_check_fail';
            }
            $api->send($tmpCode, 86, $staff->phone, ['company_name' => $company->company_name, 'reason' => $reason], $action);
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
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $success = Company::whereCompanyId($this->req->company_id)->update([
            'status' => $this->req->status
        ]);
        if ($success) {
            return $this->successJson([], '操作成功');
        }
        return $this->errorJson('操作失败！');
    }

    public function getState()
    {

        $conf = Config::get('phone.route');
        $data = [];
        foreach ($conf as $k => $v) {
            array_push($data, [
                'value' => $k,
                'name' => $v['name'],
            ]);
        }
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

