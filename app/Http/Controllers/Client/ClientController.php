<?php

namespace App\Http\Controllers\Client;

use App\Models\Channel;
use App\Models\Device;
use App\Models\PhysicsAddress;
use App\Models\User;
use App\Models\WebConfig;
use App\Service\GameApi\LrsApi;
use Illuminate\Support\Facades\Hash;
use App\Constants\ErrorCode;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class ClientController extends Base
{

    /**
     * 内部测试用
     */
    public function reqLogin()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'account' => 'required',
            'password' => 'required',
            'channelId' => 'required',
            'deviceShortId' => 'required',
        ],[
            'account.required'=>'账号不能为空!',
            'password.required'=>'密码不能为空!',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::ACCOUNT_VALID_FAILURE,
            ]);
        }
        $user = User::whereAccount($this->request->input('account'))->first();
        if (!$user) {
            return $this->json([
                'errorMessage' => 'account not find',
                'code' => ErrorCode::ACCOUNT_NOT_EXIST,
            ]);
        }
        if ($user and Hash::check($this->request->input('password'), $user->password)) {
            if ($user->lock == 2) {
                return $this->json([
                    'errorMessage' => '账号已锁定!',
                    'code' => ErrorCode::ACCOUNT_LOCK,
                ]);
            }
            $device = Device::whereDeviceId($this->request->input('deviceShortId'))->first();
            if (!$device) {
                return $this->json([
                    'errorMessage' => '设备未绑定房间',
                    'code' => ErrorCode::DEVICE_NOT_BINDING,
                ]);
            }
            if ($device->seat_num == 0 and $user->judge !== 1) {
                return $this->json([
                    'errorMessage' => '普通账号,无法登陆法官设备',
                    'code' => ErrorCode::FAIL_LOGIN_CURRENT_DEVICE,
                ]);
            }
            $channelId = $this->request->input('channelId');
            $chanel = Channel::whereChannelId($channelId)->first();
            if ($chanel) {
                //更新最后登录的渠道
                User::whereId($user->id)->update([
                    'channel_id' => $channelId,
                ]);
                $api = new LrsApi($chanel);
                return $api->loginCallBack([
                    "deviceShortId" => $device->device_id,
                    "account" => $user->account,
                    "userId" => $user->id,
                    "name" => $user->nickname,
                    "sex" => $user->sex,
                    "icon" => $user->icon ?? '',
                    "roomId" => $device->room_id, // [可选] 房间唯一id
                    "dupId" => $device->room->dup_id, // [可选] 房间对于dupId
                    "judge" => $device->seat_num == 0 ? 1 : 0, // [可选] 是否是法官，0否 1是
                    "seatIdx" => $device->seat_num, // [可选] 座位号，法官为0，其他从1开始
                    "deviceMqttTopic" => $device->room->deviceMqttTopic ?? '', // [可选]房间设备mqtt主题
                ]);
            } else {
                return $this->json([
                    'errorMessage' => '渠道不存在!',
                    'code' => ErrorCode::CHANNEL_NONENTITY,
                ]);
            }
        }
        return $this->json([
            'errorMessage' => '登录失败',
            'code' => ErrorCode::ACCOUNT_VALID_FAILURE,
        ]);
    }


    public function checkDeviceBindStatus()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'deviceId' => 'required',
        ], [
            'deviceId.required' => '设备id不能为空',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $hasItem = PhysicsAddress::wherePhysicsId($this->request->input('deviceId'))->first();
        if (!$hasItem) {
            //生成新的设备id
            $hasItem = PhysicsAddress::create([
                'physics_id' => $this->request->input('deviceId'),
            ]);
        } else {

            $device = \App\Models\Device::with(['room'=>function($r){
                $r->with('channel')->select('room_id','channel_id','room_name');
            },'store'=>function($r){
                $r->select('store_id','store_name');
            }])->whereDeviceId($hasItem->id)->first();

            if ($device) {
                return $this->json([
                    'code' => ErrorCode::SUCCESS,
                    'errorMessage' => 'success',
                    'deviceShortId' => $hasItem->id,
                    "StoreName" => $device->store->store_name,
                    "RoomName" => $device->room->room_name,
                    "RoomId" => $device->room_id,
                    "SeatIdx" => $device->seat_num, // [可选] 座位号，法官为0，其他从1开始
                    "ChannelId" => $device->room->channel_id, // [可选] 座位号，法官为0，其他从1开始
                    "GameServerAddress" => $device->room->channel?$device->room->channel->gameSrvAddr:WebConfig::getKeyByFile('GameServer.GameServerAddress'),
                ]);
            }
        }
        return $this->json([
            'errorMessage' => 'not build room',
            'code' => ErrorCode::DEVICE_NOT_BINDING,
            'deviceShortId' => $hasItem->id,
        ]);
    }

    public function conf()
    {
        $data=WebConfig::getKeyByFile('version');
        return $this->json(array_merge([
            'code' => ErrorCode::SUCCESS,
            'errorMessage' => 'success',
        ],$data));
    }

}

