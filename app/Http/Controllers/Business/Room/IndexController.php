<?php

namespace App\Http\Controllers\Business\Room;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Company;
use App\Models\Device;
use App\Models\PhysicsAddress;
use App\Models\Room;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class IndexController extends Controller
{

    public function roomList()
    {
        $data = new Room();

        $data=$data->whereCompanyId($this->loginUser->company_id)->with('devices')->with('company')->with('store')->with('billing');

        if(in_array($this->loginUser->role_id,[3,4])){
            $data=$data->whereStoreId($this->loginUser->store_id);
        }

        if ($this->req->room_name) {
            $data = $data->where('room.room_name', 'like', '%' . $this->req->room_name . '%');
        }
        if ($this->req->company_name) {
            $data = $data->where('company.company_name', 'like', '%' . $this->req->company_name . '%')->leftJoin('company', 'room.company_id', '=', 'company.company_id');
        }
        if ($this->req->store_name) {
            $data = $data->where('store.store_name', 'like', '%' . $this->req->store_name . '%')->leftJoin('store', 'room.store_id', '=', 'store.company_id');
        }
        $data = $data->orderBy('room.room_id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $game=[
            'playing_count'=>100,//游戏中人数
            'use_room_count'=>Room::whereIsUse(1)->count(),//使用中房间数
            'leisure_room_count'=>Room::whereIsUse(0)->count(),//空闲房间数
        ];
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 添加房间
     */
    public function addRoom()
    {
        $validator = Validator::make($this->req->all(), [
            'room_name' =>['required','max:30'],
            'seats_num' => 'required|numeric|max:16|min:1',
            'description' => 'max:150',
            'billing_id' => 'required',
            'storeArr' => 'required|array',
        ], [
            'room_name.required' => '房间名字必须填写！',
            'room_name.max' => '房间名字最长30字符！',
            'seats_num.required' => '座位数必填',
            'seats_num.max' => '座位数最大值16！',
            'seats_num.min' => '座位数最小值是1',
            'describe.max' => '描述不能超过150字！',
            'billing_id.required' => '计费模式，不能为空！',
            'storeArr.required' => '请选择门店！',
            'storeArr.array' => '请选择门店是一个数组!',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $storeArr=$this->req->input('storeArr',[]);
        if(count($storeArr)!==2){
            return $this->errorJson('你未请选择门店!');
        }
        $data = $this->req->except('use_time','storeArr','devices','room_id','seat_num','device_id','delDevicesIds');
        DB::beginTransaction();
        $data['company_id']=$storeArr[0];
        $data['store_id']=$storeArr[1];
        $room = Room::create($data);

        list($status,$deviceData)=$this->checkDevices($this->req->devices,$room);
        if(!$status){
            DB::rollBack();
            return $this->errorJson('设备id重复'.$deviceData.',入库失败!',10001);
        }
        //设备入库
        $flag= Device::insert($deviceData);
        if ($room and $flag) {
            DB::commit();
            return $this->successJson([], '操作成功');
        } else {
            DB::rollBack();
            return $this->errorJson('入库失败');
        }
    }

    /**
     * 批量添加设备id
     * @param $arr
     */
    private function checkDevices($arr,$room){
        //删除该房间所有设备，重新install
        Device::whereRoomId($room->room_id)->delete();
        $data=[];
        $message='';
        foreach ($arr as $val){
            $item=[
                'device_id'=>$val['device_id'],
                'seat_num'=>$val['seat_num'],
                'device_name'=>$val['device_name'],
                'room_id'=>$room->room_id,
                'store_id'=>$room->store_id,
                'company_id'=>$room->company_id,
            ];
            $hasItem=Device::whereDeviceId($item['device_id'])->first();
            if($hasItem){
                $message.='('.$val['device_name'].':'.$val['device_id'].')-重复录入';
                continue;
            }
           $hasPy=PhysicsAddress::whereId($item['device_id'])->first();
            if($hasPy){
                array_push($data,$item);
            }else{
                $message.='('.$val['device_name'].':'.$val['device_id'].')-设备短id不存在';
            }
        }
        if($message){
            return [false,$message];
        }
        return [true,$data];
    }
    public function editRoom(){
        $validator = Validator::make($this->req->all(), [
            'room_id' => 'required',
            'room_name' => 'required|max:30',
            'seats_num' => 'required|numeric|max:16|min:1',
            'description' => 'max:150',
            'billing_id' => 'required',
            'storeArr' => 'required|array',
        ], [
            'room_.required' => '房间id必须存在',
            'room_name.required' => '房间名字必须填写！',
            'room_name.max' => '房间名字最长30字符！',
            'seats_num.required' => '座位数必填',
            'seats_num.max' => '座位数最大值16！',
            'seats_num.min' => '座位数最小值是1',
            'describe.max' => '描述不能超过150字！',
            'billing_id.required' => '计费模式，不能为空！',
            'storeArr.required' => '请选择门店！',
            'storeArr.array' => '请选择门店是一个数组!',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $storeArr=$this->req->input('storeArr',[]);
        if(count($storeArr)!==2){
            return $this->errorJson('你未请选择门店!');
        }
        $data = $this->req->except('use_time','storeArr','devices','seat_num','device_id','delDevicesIds');
        $data['company_id']=$storeArr[0];
        $data['store_id']=$storeArr[1];
        DB::beginTransaction();
        $room = Room::whereRoomId($this->req->room_id)->first();
        $room->fill($data);
        $room->save();
        list($status,$deviceData)=$this->checkDevices($this->req->devices,$room);
        if(!$status){
            DB::rollBack();
            return $this->errorJson('设备id重复'.$deviceData.',入库失败!',10001);
        }
        //设备入库
        $flag= Device::insert($deviceData);
        if ($room and $flag) {
            DB::commit();
            return $this->successJson([], '修改成功');
        } else {
            DB::rollBack();
            return $this->errorJson('修改失败');
        }
    }

    public function companyAndRoomList()
    {
        $validator = Validator::make($this->req->all(), [
            'parent_id' => 'required|numeric',
            'level' => 'required|numeric',
        ], [
            'parent_id.required' => 'parent_id必须',
            'parent_id.numeric' => 'parent_id一个数字',
            'level.required' => 'level必须',
            'level.numeric' => 'level一个数字',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data=[];
        switch ($this->req->level){
            case 0:
                $company = Company::orderBy('company_id','desc')->limit(50)->get();
                $data = [];
                foreach ($company as $val) {
                    array_push($data, [
                        'value' => $val->company_id,
                        'label' => $val->company_name,
                        'parent_id' => $val->company_id,
                        'level' => 0,
                        'leaf' =>false
                    ]);
                }
                break;
            case 1:
                $company = Store::whereCompanyId($this->req->parent_id)->orderBy('store_id','desc')->get();
                $data = [];
                foreach ($company as $val) {
                    array_push($data, [
                        'value' => $val->store_id,
                        'label' => $val->store_name,
                        'parent_id' => $val->store_id,
                        'level' => 1,
                        'leaf' =>true
                    ]);
                }
                break;
        }
        $assign = compact('data');
        return $this->successJson($assign);
    }





}

