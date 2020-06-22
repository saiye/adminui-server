<?php

namespace App\Http\Controllers\Cp\Store;

use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Staff;
use App\Models\Store;
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

    public function storeList()
    {
        $data = new Store();
        $data = $data->with('staff')->with('company')->with('region')->with('province')->with('city');
        if ($this->req->store_id) {
            $data = $data->whereStoreId($this->req->store_id);
        }
        if ($this->req->store_name) {
            $data = $data->where('store_name', 'like', '%' . $this->req->store_name . '%');
        }
        if ($this->req->address) {
            $data = $data->where('address', 'like', '%' . $this->req->address . '%');
        }
        if ($this->req->state_id) {
            $data = $data->whereStateId($this->req->state_id);
        }
        if ($this->req->province_id) {
            $data = $data->whereProvinceId($this->req->province_id);
        }
        if ($this->req->city_id) {
            $data = $data->whereCityId($this->req->city_id);
        }
        if ($this->req->company_id) {
            $data = $data->whereCompanyId($this->req->company_id);
        }
        if ($this->req->staff_id) {
            $data = $data->whereStaffId($this->req->staff_id);
        }
        if ($this->req->check) {
            $data = $data->whereIn('check',$this->req->check);
        }
        if($this->req->listDate){
            $data = $data->whereBetween('store.created_at',$this->req->listDate);
        }
        if($this->req->company_name){
            $data=$data->where('company.company_name','like','%'.$this->req->company_name.'%')->leftJoin('company','store.company_id','=','company.company_id');
        }
        if($this->req->real_name){
            $data=$data->where('staff.real_name','like','%'.$this->req->real_name.'%')->leftJoin('staff','store.staff_id','=','staff.staff_id');
        }
        $data = $data->orderBy('store.store_id', 'desc')->paginate($this->req->input('limit',15))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 添加门店
     */
    public function addStore()
    {
        $validator = Validator::make($this->req->all(), [
            'store_name' => 'required|max:30',
            'area' => 'required|array',
            'address' => 'required|max:100',
            'company_id' => 'required',
            'describe' => 'max:100',
            'account' => ['regex:/^[0-9A-Za-z]+$/', 'required', 'max:20', 'unique:staff,account'],
            'password' => 'required|max:100',
            'real_name' => 'required|max:100',
            'sex' => 'required|in:1,2',
            'phone' => ['required', 'regex:/^1[3|4|5|6|7|8|9][0-9]{9}$/', 'unique:staff,phone'],
        ], [
            'company_id.required' => '商户必须选择！',
            'store_name.required' => '门店名称，不能为空！',
            'store_name.max' => '门店名称最大长度30！',
            'area.required' => '所在地区，不能为空！',
            'area.array' => '所在地区，必须是个数组！',
            'describe.max' => '店面描述不能超过100字！',
            'address.required' => '地址，不能为空！',
            'address.max' => '地址最大长度100！',
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
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $area = $this->req->area;
        $region_id = $city_id = $province_id = 0;
        if (count($area) == 3) {
            $province_id = $area[0];
            $city_id = $area[1];
            $region_id = $area[2];
        }

        DB::beginTransaction();
        //联系人入库
        $staff = [
            'account' => $this->req->account,
            'real_name' => $this->req->real_name,
            'sex' => $this->req->sex,
            'phone' => $this->req->phone,
            'lock' => 1,
            'type' => 1,
            'company_id' => $this->req->company_id,
            'password' => Hash::make($this->req->password),
        ];
        $staffObj = Staff::create($staff);
        $save_staff = $staffObj->save();


        //商家入库
        $data['store_name'] = $this->req->store_name;
        $data['region_id'] = $region_id;
        $data['province_id'] = $province_id;
        $data['city_id'] = $city_id;
        $data['address'] = $this->req->address;
        $data['company_id'] = $this->req->company_id;
        $data['describe'] = $this->req->describe;
        $data['staff_id'] = $staffObj->staff_id;
        $store = Store::create($data);
        $store->save();
        //联系人绑定到该店面
        $staffObj->store_id = $store->store_id;
        $save_store = $staffObj->save();

        if ($save_store and $save_staff) {
            DB::commit();
            return $this->successJson([], '操作成功');
        } else {
            DB::rollBack();
            return $this->errorJson('入库失败');
        }
    }

    /**
     * 审核门店
     */
    public function checkStore()
    {
        $validator = Validator::make($this->req->all(), [
            'store_id' => 'required|numeric',
            'check' => 'required|numeric',
            'reason' => 'max:100',
        ], [
            'store_id.required' => '商户id不能为空',
            'store_id.numeric' => '商户id是一个数字',
            'check.required' => '审核状态必须',
            'check.numeric' => '审核状态是一个数字',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $success = Store::whereStoreId($this->req->store_id)->update([
            'check' => $this->req->check,
            'reason' => $this->req->reason??'',
        ]);
        if ($success) {
            return $this->successJson([], '操作成功');
        }
        return $this->errorJson('审核失败！');
    }


}

