<?php

namespace App\Http\Controllers\Business\Order;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Order;
use Validator;
use Illuminate\Support\Facades\Config;

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
        $data = $data->orderBy('order_id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 后台下单
     */
    public function createOrder()
    {
        return $this->errorJson('订单不存在');
    }

    /**
     * 退款
     */
    public function refund()
    {
        return $this->errorJson('订单不存在');
    }

    /**
     * 搜索条件
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectConfig()
    {
        $data = Config::get('pay.selectConf');

        return $this->successJson($data);
    }

    public function orderDetail()
    {
        $validator = Validator::make($this->req->all(), [
            'order_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $data = Order::whereCompanyId($this->loginUser->company_id)->whereId($this->req->order_id)->first();
        if (!$data) {
            return $this->errorJson('订单不存在');
        }
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 修改订单为完成状态
     */
    public function setOrder()
    {
        $validator = Validator::make($this->req->all(), [
            'order_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $data = Order::whereCompanyId($this->loginUser->company_id)->whereOrderId($this->req->order_id)->first();
        if (!$data) {
            return $this->errorJson('订单不存在');
        }
        $data->status = 3;
        $data->save();
        return $this->successJson([], '操作成功！');
    }
}

