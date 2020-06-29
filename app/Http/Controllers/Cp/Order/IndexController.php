<?php

namespace App\Http\Controllers\Cp\Order;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Room;
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

    public function orderList()
    {
        $data = new Order();
        if ($this->req->order_sn) {
            $data = $data->where('order_sn', $this->req->order_sn);
        }
        if ($this->req->room_id) {
            $data = $data->where('room_id', $this->req->room_id);
        }
        if ($this->req->store_id) {
            $data = $data->where('store_id', $this->req->store_id);
        }
        if ($this->req->company_id) {
            $data = $data->where('company_id', $this->req->company_id);
        }
        if ($this->req->staff_id) {
            $data = $data->where('staff_id', $this->req->staff_id);
        }
        $data = $data->orderBy('roder_id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 添加房间
     */
    public function addOrder()
    {
        $validator = Validator::make($this->req->all(), [
            'room_id' => 'required',
            'store_id' => 'required',
            'company_id' => 'required',
            'staff_id' => 'required',
        ], [
            'room_id.required' => '房间号不能为空！',
            'store_id.required' => '门店id，不能为空！',
            'company_id.required' => '商户id，不能为空！',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data = $this->req->except('play_type', 'play_time', 'play_status');
        $room = Room::create($data);
        if ($room) {
            return $this->successJson([], '操作成功');
        } else {
            return $this->errorJson('入库失败');
        }
    }


}

