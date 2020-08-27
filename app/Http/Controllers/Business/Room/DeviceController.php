<?php

namespace App\Http\Controllers\Business\Room;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
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

}

