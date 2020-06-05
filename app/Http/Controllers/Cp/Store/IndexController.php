<?php

namespace App\Http\Controllers\Cp\Store;

use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Store;
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
            $data = $data->whereCheck($this->req->check);
        }
        $data = $data->orderBy('id', 'desc')->paginate(30)->appends($this->req->except('page'));
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
            'state_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'address' => 'required|max:100',
            'company_id' => 'required',
            'describe' => 'required',
            'check' => 'required',
            'staff_id' => 'required',
        ], [

            'store_name.required' => '门店名称，不能为空！',
            'store_name.max' => '门店名称最大长度30！',
            'state_id.required' => '所在国家，不能为空！',
            'province_id.required' => '所在省份，不能为空！',
            'city_id.required' => '所在城市，不能为空！',
            'address.required' => '地址，不能为空！',
            'address.max' => '地址最大长度100！',
            'account.required' => '账号，不能为空！',
            'password.required' => '密码，不能为空！',
            'real_name.required' => '联系人，不能为空！',
            'sex.required' => '性别必须选择',
            'sex.in' => '性别选择错误',

        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }

        //   DB::beginTransaction();
        //商家入库
        $data['store_name'] = $this->req->store_name;
        $data['state_id'] = $this->req->state_id;
        $data['province_id'] = $this->req->province_id;
        $data['city_id'] = $this->req->city_id;
        $data['address'] = $this->req->address;
        $data['company_id'] = $this->req->company_id;
        $data['describe'] = $this->req->describe;
        $store = Store::create($data);
        $store->save();

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
        $staffObj = Staff::create($staff)->save();
        //更新商户表
        $store->staff_id = $staffObj->staff_id;
        $store->save();

        //DB::rollBack();

        //  DB::commit();

        return $this->successJson([], '操作成功');
    }

    /**
     * 审核门店
     */
    public function checkStore()
    {
        $validator = Validator::make($this->req->all(), [
            'store_id' => 'required|numeric',
            'check' => 'required|numeric',

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
            'check' => $this->req->req->check,
        ]);
        if ($success) {
            return $this->successJson([], '操作成功');
        }
        return $this->errorJson('审核失败！');
    }


}

