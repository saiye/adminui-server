<?php

namespace App\Http\Controllers\Cp\Store;

use App\Constants\CacheKey;
use App\Constants\PaginateSet;
use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Image;
use App\Models\Staff;
use App\Models\Store;
use App\Models\StoreTag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
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
        $data = $data->with('staff')->with(['image' => function ($r) {
            $r->whereType(2)->whereIsDel(0);
        }])->with('tags')->with('company')->with('region')->with('province')->with('city');

        if ($this->req->company_id) {
            $data = $data->whereCompanyId($this->req->company_id);
        }
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
        if ($this->req->staff_id) {
            $data = $data->whereStaffId($this->req->staff_id);
        }
        if ($this->req->check) {
            $data = $data->whereIn('check', $this->req->check);
        }
        if ($this->req->listDate) {
            $data = $data->whereBetween('store.created_at', $this->req->listDate);
        }
        if ($this->req->company_name) {
            $data = $data->where('company.company_name', 'like', '%' . $this->req->company_name . '%')->leftJoin('company', 'store.company_id', '=', 'company.company_id');
        }
        if ($this->req->real_name) {
            $data = $data->where('staff.real_name', 'like', '%' . $this->req->real_name . '%')->leftJoin('staff', 'store.staff_id', '=', 'staff.staff_id');
        }
        $data = $data->orderBy('store.store_id', 'desc')->paginate($this->req->input('limit', $limit = $this->req->input('limit', PaginateSet::LIMIT)))->appends($this->req->except('page'));
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
            'company_id' => 'required',
            'imageData' => 'array',
            'area' => 'required|array',
            'address' => 'required|max:100',
            'describe' => 'max:100',
            'account' => ['regex:/^[0-9A-Za-z]+$/', 'required', 'max:20', 'unique:staff,account'],
            'password' => 'required|max:100',
            'real_name' => 'required|max:100',
            'sex' => 'required|in:1,2',
            'tags' => 'array',
            'phone' => ['required', 'regex:/^1[3|4|5|6|7|8|9][0-9]{9}$/'],
            'point' => ['required', 'regex:/^([-+])?(((\d|[1-9]\d|1[0-7]\d|0{1,3})\.\d{0,6})|(\d|[1-9]\d|1[0-7]\d|0{1,3})|180\.0{0,6}|180),([-+])?([0-8]?\d{1}\.\d{0,6}|90\.0{0,6}|[0-8]?\d{1}|90)$/'],
        ], [
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
            'tags.array' => '标签格式错误!',
            'point.required' => '请输入经纬度!',
            'point.regex' => '请输入正确的经纬度!',
            'company_id.required' => '请选择商户!',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }

        //经纬度处理
        $point = $this->req->input('point');
        $pointArr = explode(',', $point);

        $company_id = $this->req->input('company_id');

        $area = $this->req->input('area');
        $region_id = $city_id = $province_id = 0;
        if (count($area) == 3) {
            $province_id = $area[0];
            $city_id = $area[1];
            $region_id = $area[2];
        }
        $imageData = $this->req->input('imageData', []);
        DB::beginTransaction();
        //联系人入库
        $staff = [
            'account' => $this->req->account,
            'real_name' => $this->req->real_name,
            'sex' => $this->req->sex,
            'phone' => $this->req->phone,
            'lock' => 1,
            'role_id' => 3,
            'company_id' => $company_id,
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
        $data['company_id'] = $company_id;
        $data['describe'] = $this->req->describe;
        $data['staff_id'] = $staffObj->staff_id;
        $data['lon'] = $pointArr[0];
        $data['lat'] = $pointArr[1];
        $store = Store::create($data);
        $tags = $this->req->input('tags', []);
        if ($tags) {
            $config = Config::get('deploy.store_tag');
            $tagsArr = [];
            foreach ($tags as $tg) {
                if (isset($config[$tg])) {
                    array_push($tagsArr, [
                        'tag_id' => $tg,
                        'store_id' => $store->store_id,
                        'tag_name' => $config[$tg],
                    ]);
                }
            }
            if ($tagsArr) {
                StoreTag::insert($tagsArr);
            }
        }
        if ($imageData) {
            Image::whereIn('id', $imageData)->whereForeignId(0)->whereType(2)->update([
                'foreign_id' => $store->store_id,
            ]);
        }
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
     * 添加门店
     */
    public function editStore()
    {
        $validator = Validator::make($this->req->all(), [
            'store_id' => 'required',
            'company_id' => 'required',
            'imageData' => 'array',
            'store_name' => 'required|max:30',
            'area' => 'required|array',
            'address' => 'required|max:100',
            'describe' => 'max:100',
            'account' => ['regex:/^[0-9A-Za-z]+$/', 'required', 'max:20'],
            'password' => 'nullable|min:6|max:100',
            'real_name' => 'required|max:100',
            'sex' => 'required|in:1,2',
            'tags' => 'array',
            'phone' => ['required', 'regex:/^1[3|4|5|6|7|8|9][0-9]{9}$/'],
            'point' => ['required', 'regex:/^([-+])?(((\d|[1-9]\d|1[0-7]\d|0{1,3})\.\d{0,6})|(\d|[1-9]\d|1[0-7]\d|0{1,3})|180\.0{0,6}|180),([-+])?([0-8]?\d{1}\.\d{0,6}|90\.0{0,6}|[0-8]?\d{1}|90)$/'],
        ], [
            'company_id.required' => '请选择商户！',
            'store_name.required' => '门店名称，不能为空！',
            'store_name.max' => '门店名称最大长度30！',
            'area.required' => '所在地区，不能为空！',
            'area.array' => '所在地区，必须是个数组！',
            'describe.max' => '店面描述不能超过100字！',
            'address.required' => '地址，不能为空！',
            'address.max' => '地址最大长度100！',
            'account.required' => '账号，不能为空！',
            'account.max' => '店长账号最大长度20！',
            'account.regex' => '店长账号必须是字母数字的组合！',
            'account.unique' => '店长账号已存在，请用其他账号注册！',
            'password.required' => '店长密码，不能为空！',
            'password.min' => '店长密码，不能小于6位！',
            'real_name.required' => '联系人，不能为空！',
            'sex.required' => '性别必须选择',
            'sex.in' => '性别选择错误',
            'phone.required' => '手机号码，不能为空！',
            'phone.regex' => '你输入的不是手机号码',
            'phone.unique' => '你输入的手机号码已存在!',
            'tags.array' => '标签格式错误!',
            'point.required' => '请输入经纬度!',
            'point.regex' => '请输入正确的经纬度!',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        //经纬度处理
        $point = $this->req->input('point');
        $imageData = $this->req->input('imageData', []);

        $pointArr = explode(',', $point);
        $company_id = $this->req->input('company_id');

        $area = $this->req->area;
        $region_id = $city_id = $province_id = 0;
        if (count($area) == 3) {
            $province_id = $area[0];
            $city_id = $area[1];
            $region_id = $area[2];
        }
        $store = Store::whereStoreId($this->req->store_id)->first();
        if (!$store) {
            return $this->errorJson('店面不存在!', 2);
        }
        $staffObj = Staff::whereAccount($this->req->account)->first();

        if ($staffObj and $staffObj->store_id !== $this->req->store_id) {
            return $this->errorJson('该账号已占用，修改失败！', 2);
        }
        DB::beginTransaction();
        if ($staffObj) {
            $staffData = [
                'real_name' => $this->req->real_name,
                'sex' => $this->req->sex,
                'phone' => $this->req->phone,
                'lock' => 1,
                'role_id' => 3,
                'company_id' => $company_id,
            ];
            if ($this->req->password) {
                $staffObj['password'] = Hash::make($this->req->password);
            }
            //修改
            $staffObj->fill($staffData)->save();
        } else {
            //联系人入库
            $staff = [
                'account' => $this->req->account,
                'real_name' => $this->req->real_name,
                'sex' => $this->req->sex,
                'phone' => $this->req->phone,
                'lock' => 1,
                'role_id' => 3,
                'company_id' => $company_id,
                'store_id' => $this->req->store_id,
                'password' => Hash::make($this->req->password),
            ];
            $staffObj = Staff::create($staff);
        }
        //商家入库
        $data['store_name'] = $this->req->store_name;
        $data['region_id'] = $region_id;
        $data['province_id'] = $province_id;
        $data['city_id'] = $city_id;
        $data['address'] = $this->req->address;
        $data['company_id'] = $company_id;
        $data['describe'] = $this->req->describe;
        $data['staff_id'] = $staffObj->staff_id;
        $data['lon'] = $pointArr[0];
        $data['lat'] = $pointArr[1];
        $store->fill($data);
        $save_store = $store->save();
        if ($imageData) {
            Image::whereIn('id', $imageData)->whereForeignId(0)->whereType(2)->update([
                'foreign_id' => $store->store_id,
            ]);
        }
        $tags = $this->req->input('tags', []);
        if ($tags) {
            $config = Config::get('deploy.store_tag');
            $tagsArr = [];
            foreach ($tags as $tg) {
                if (isset($config[$tg])) {
                    array_push($tagsArr, [
                        'tag_id' => $tg,
                        'store_id' => $store->store_id,
                        'tag_name' => $config[$tg],
                    ]);
                }
            }
            if ($tagsArr) {
                StoreTag::whereStoreId($this->req->store_id)->delete();
                StoreTag::insert($tagsArr);
            }
        }
        if ($save_store) {
            DB::commit();
            return $this->successJson([], '修改成功');
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
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $item = Store::whereStoreId($this->req->store_id)->first();
        if ($item) {
            $item->check = $this->req->check;
            $item->reason = $this->req->reason ?? '';
            $success = $item->save();
            $checkStaff = Staff::whereStaffId($item->staff_id)->update([
                'lock' => 1,
            ]);
            if ($success and $checkStaff) {
                return $this->successJson([], '操作成功');
            }
        }
        return $this->errorJson('审核失败！');
    }


    public function tagList()
    {
        $config = Config::get('deploy.store_tag');
        $data = [];
        foreach ($config as $k => $v) {
            array_push($data, [
                'k' => $k,
                'v' => $v,
            ]);
        }
        return $this->successJson($data);
    }

    /**
     * 关闭店铺
     */
    public function closeStore()
    {
        $validator = Validator::make($this->req->all(), [
            'store_id' => 'required|numeric',
            'close' => 'required|numeric|in:0,1',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }

        $upStore = Store::whereStoreId($this->req->store_id)->update([
            'is_close' => $this->req->close
        ]);
        if ($upStore) {
            return $this->successJson([], '操作成功');
        }
        return $this->errorJson('操作失败');
    }


}

