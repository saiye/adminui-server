<?php

namespace App\Http\Controllers\Cp\Order;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Room;
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
        $data = $data->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

}

