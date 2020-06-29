<?php

namespace App\Http\Controllers\Cp\Game;

use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Channel;
use App\Models\Device;
use App\Models\PhysicsAddress;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;
use Illuminate\Support\Facades\Cache;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class ClientController extends Controller
{
    public function login()
    {
        $validator = Validator::make($this->req->all(), [
            'account' => 'required',
            'password' => 'required',
            'channelId' => 'required',
            'deviceShortId' => 'required',
        ], [
            'account.required' => '账号不能为空',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errorMessage' => '参数错误:' . $this->validatorMessage($validator->errors()->toArray()),
                'code' => -1,
            ]);
        }
        $user = User::whereAccount($this->req->account)->first();
        if (!$user) {
            return response()->json([
                'errorMessage' => '账号不存在',
                'code' => -1,
            ]);
        }
        if ($user and Hash::check($this->req->password, $user->password)) {
            if ($user->lock == 2) {
                return response()->json([
                    'errorMessage' => '账号已锁定!',
                    'code' => -1,
                ]);
            }
            $device = Device::whereDeviceId($this->req->deviceShortId)->first();
            if (!$device) {
                return response()->json([
                    'errorMessage' => '设备未绑定房间',
                    'code' => -1,
                ]);
            }
            if ($device->seat_num == 0 and $user->judge !== 1) {
                return response()->json([
                    'errorMessage' => '该账号没有权限登录当前设备,因为普通账号,无法登陆法官设备',
                    'code' => -4,
                ]);
            }
            $chanel = Channel::whereChannelId($this->req->channelId)->first();
            $token = Str::random(20);
            Cache::put($token, $user, 60);
            if ($chanel) {
                try {
                    $res = post_curl('http://192.168.1.3:7683/loginCallback', [
                        "deviceShortId" => $device->device_id,
                        "account" => $user->account,
                        "userId" => $user->id,
                        "name" => $user->nickname,
                        "sex" => $user->sex,
                        "icon" => '',
                    ]);
                } catch (\Exception $e) {

                }
                return response()->json([
                    'errorMessage' => 'success',
                    "userId" => $user->id,
                    "name" => $user->nickname,
                    "sex" => $user->sex,
                    "icon" => $user->icon,
                    "token" => $token,
                    "gameSrvAddr" => $chanel->gameSrvAddr,
                    'code' => 0
                ]);
            } else {
                return response()->json([
                    'errorMessage' => '渠道不存在!',
                    'code' => -1,
                ]);
            }
        }
        return response()->json([
            'errorMessage' => '登录失败',
            'code' => -1,
        ]);
    }

    public function queryDeviceRoomData()
    {
        $validator = Validator::make($this->req->all(), [
            'deviceShortId' => 'required',
        ], [
            'deviceShortId.required' => '设备短id不能为空',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errorMessage' => '参数错误',
                'code' => -1,
            ]);
        }
        $device = Device::whereDeviceId($this->req->deviceShortId)->first();
        if ($device) {
            return response()->json([
                'errorMessage' => '',
                "roomId" => $device->room_id, // [可选] 房间唯一id
                "dupId" => $device->room->dup_id, // [可选] 房间对于dupId
                "judge" => $device->seat_num == 0 ? 1 : 0, // [可选] 是否是法官，0否 1是
                "seatIdx" => $device->seat_num, // [可选] 座位号，法官为0，其他从1开始
                'code' => 0,
            ]);
        }
        return response()->json([
            'errorMessage' => '设备id未绑定房间',
            'code' => -1,
        ]);

    }

    public function checkDevice()
    {
        $validator = Validator::make($this->req->all(), [
            'deviceId' => 'required',
        ], [
            'deviceId.required' => '设备id不能为空',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errorMessage' => '参数错误',
                'code' => -1,
            ]);
        }
        $hasItem = PhysicsAddress::wherePhysicsId($this->req->deviceId)->first();
        if (!$hasItem) {
            //生成新的设备id
            $hasItem = PhysicsAddress::create([
                'physics_id' => $this->req->deviceId,
            ]);
        } else {
            $device = Device::whereDeviceId($hasItem->id)->first();
            if ($device) {
                return response()->json([
                    'errorMessage' => 'success',
                    'deviceShortId' => $hasItem->id,
                    "StoreName" => $device->store->store_name,
                    "RoomName" => $device->room->room_name,
                    "RoomId" => $device->room_id,
                    'LoginUrl'=>'',
                    'GameServerAddress'=>'',
                    'code' => 0,
                ], 200);
            }
        }
        return response()->json([
            'errorMessage' => '未绑定房间',
            'code' => -1,
            'deviceShortId' => $hasItem->id,
        ]);
    }

    public function callLogin(){
        $url='';
        $data='';
        return response()->json([
            'errorMessage' =>'',
            'code' => -1,
            'data' =>$data,
        ]);

    }
}

