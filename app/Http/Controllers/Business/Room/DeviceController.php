<?php

namespace App\Http\Controllers\Business\Room;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Billing;
use App\Models\Device;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class DeviceController extends Controller
{

    public function deviceList()
    {
        $data = new Device();

        if ($this->req->id) {
            $data = $data->where('id', $this->req->id);
        }
        if ($this->req->device_id) {
            $data = $data->where('device_id', $this->req->device_id);
        }
        if ($this->req->device_name) {
            $data = $data->where('device_name', 'like', '%' . $this->req->device_name . '%');
        }
        if ($this->req->room_id) {
            $data = $data->where('room_id', $this->req->room_id);
        }
        $data = $data->orderBy('id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 添加设备
     */
    public function addDevice()
    {
        $validator = Validator::make($this->req->all(), [
            'device_id' => 'required|max:128',
            'device_name' => 'required|max:15',
            'seat_num' => 'required|max:16|min:2',
            'room_id' => 'required',
            'store_id' => 'required',
            'company_id' => 'required',
        ], [
            'device_id.required' => '设备id必须填写！',
            'device_id.max' => '设备id最长128字符！',
            'device_name.required' => '设备名称必须填写！',
            'device_name.max' => '设备名称最长50字符！',
            'seat_num.max' => '座位号最大16！',
            'seat_num.min' => '座位号最小2！',
            'room_id.required' => '房间号必须填写！',
            'company_id.required' => '商户id不能为空！',
            'store_id.required' => '门店id不能为空！',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data = $this->req->except('id');
        $room = Device::create($data);
        if ($room) {
            return $this->successJson([], '操作成功');
        } else {
            return $this->errorJson('入库失败');
        }
    }
}

