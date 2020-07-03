<?php

namespace App\Http\Controllers\Client;

use App\Models\Channel;
use App\Models\Device;
use App\Models\PhysicsAddress;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Constants\ErrorCode;
use GuzzleHttp\Client;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class ClientController extends Base
{

    /**
     * 【测试⽤接⼝，正式环境不可⽤】
     */
    public function reqLogin()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'account' => 'required',
            'password' => 'required',
            'channelId' => 'required',
            'deviceShortId' => 'required',
        ], [
            'account.required' => '账号不能为空',
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
                'errorMessage' => '账号不存在',
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
            $chanel = Channel::whereChannelId($this->request->input('channelId'))->first();
            if ($chanel) {
                $url = $chanel->loginCallBackAddr;
                // $url='http://192.168.1.78:9502/user/callLoginTest';
                try {
                    $client = new Client([
                        'timeout' => 3,
                    ]);
                    $response = $client->post($url, [
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                        'json' => [
                            "deviceShortId" => $device->device_id,
                            "account" => $user->account,
                            "userId" => $user->id,
                            "name" => $user->nickname,
                            "sex" => $user->sex,
                            "icon" => '',
                            "roomId" => $device->room_id, // [可选] 房间唯一id
                            "dupId" => $device->room->dup_id, // [可选] 房间对于dupId
                            "judge" => $device->seat_num == 0 ? 1 : 0, // [可选] 是否是法官，0否 1是
                            "seatIdx" => $device->seat_num, // [可选] 座位号，法官为0，其他从1开始
                        ]
                    ]);

                    if ($response->getStatusCode() == 200) {
                        $res = json_decode($response->getBody()->getContents(), true);
                        if (isset($res['code']) and $res['code'] == 0) {
                            return $this->json([
                                'errorMessage' => 'success',
                                'gameSrvAddr' => $chanel->gameSrvAddr,
                                'code' => ErrorCode::SUCCESS,
                            ]);
                        } else {
                            return $this->json([
                                'errorMessage' => 'call back return code is not 0',
                                'code' => ErrorCode::THREE_FAIL,
                            ]);
                        }

                    } else {
                        return $this->json([
                            'errorMessage' => 'The http status code is not 200',
                            'code' => ErrorCode::SERVER_ERROR,
                            'url' => $url,
                        ]);
                    }
                } catch (\Exception $e) {
                    return $this->json([
                        'errorMessage' => $e->getMessage(),
                        'code' => ErrorCode::CONNECTION_TIMEOUT,
                        'url' => $url,
                    ]);
                }
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

    public function queryDeviceRoomData()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'deviceShortId' => 'required',
        ], [
            'deviceShortId.required' => '设备短id不能为空',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $device = Device::where('device_id', $this->request->input('deviceShortId'))->first();
        if ($device) {
            return $this->json([
                'errorMessage' => '',
                "roomId" => $device->room_id, // [可选] 房间唯一id
                "dupId" => $device->room->dup_id, // [可选] 房间对于dupId
                "judge" => $device->seat_num == 0 ? 1 : 0, // [可选] 是否是法官，0否 1是
                "seatIdx" => $device->seat_num, // [可选] 座位号，法官为0，其他从1开始
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        return $this->json([
            'errorMessage' => '设备id未绑定房间',
            'code' => ErrorCode::DEVICE_NOT_BINDING,
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
            $device = Device::whereDeviceId($hasItem->id)->first();
            if ($device) {
                return $this->json([
                    'code' => ErrorCode::SUCCESS,
                    'errorMessage' => 'success',
                    'deviceShortId' => $hasItem->id,
                    "StoreName" => $device->store->store_name,
                    "RoomName" => $device->room->room_name,
                    "RoomId" => $device->room_id,
                    "LoginUrl" => 'https://192.168.1.71/user/login',
                    "GameServerAddress" => '47.115.45.34:10002',
                ]);
            }
        }
        return $this->json([
            'errorMessage' => 'not build room',
            'code' => ErrorCode::DEVICE_NOT_BINDING,
            'deviceShortId' => $hasItem->id,
        ]);
    }


}

