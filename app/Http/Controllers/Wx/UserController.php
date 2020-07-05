<?php

namespace App\Http\Controllers\Wx;

use App\Jobs\CallBackGameLogin;
use App\Models\Channel;
use App\Models\Device;
use App\Service\LoginApi\LoginApi;
use GuzzleHttp\Client;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class UserController extends Base
{
    /**
     * 微信小程序登录接口
     */
    public function login(LoginApi $loginApi)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            //'scene' => 'required',
            'js_code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        //效验用户，获取用户信息
        list($status, $message, $user) = $loginApi->getUser();

        if ($status !== 0) {
            return $this->json([
                'errorMessage' => $message,
                'code' => ErrorCode::ACCOUNT_NOT_EXIST,
            ]);
        }
        if ($user->lock == 2) {
            return $this->json([
                'errorMessage' => '账号已锁定!',
                'code' => ErrorCode::ACCOUNT_LOCK,
            ]);
        }
     //   $scene = $this->request->input('scene');
        $scene = scene_encode([
            'd'=>1026,
            'c'=>1,
        ]);
        if ($scene) {
            //存在则需要回调游戏登录地址
            $data = scene_decode($scene);
            $deviceShortId = $data['d'] ?? 0;
            $channelId = $data['c'] ?? 0;
            if (!is_numeric($deviceShortId) or $deviceShortId < 1) {
                return $this->json([
                    'errorMessage' => 'scene值错误!',
                    'code' => ErrorCode::ACCOUNT_NOT_EXIST,
                ]);
            }
            $device = Device::whereDeviceId($deviceShortId)->first();
            if (!$device) {
                return $this->json([
                    'errorMessage' => '设备未绑定房间',
                    'code' => ErrorCode::DEVICE_NOT_BINDING,
                ]);
            }
            if ($device->seat_num == 0 and $user->judge !== 1) {
                return $this->json([
                    'errorMessage' => '普通账号,无法登陆法官设备',
                    'code' => ErrorCode::ACCOUNT_NO_PREVILEGE,
                ]);
            }

            $channel = Channel::whereChannelId($channelId)->first();
            if ($channel) {
                $url = $channel->loginCallBackAddr;
                dispatch(new CallBackGameLogin($url, [
                    "deviceShortId" => $device->device_id,
                    "account" => $user->account,
                    "userId" => $user->id,
                    "name" => $user->nickname,
                    "sex" => $user->sex,
                    "icon" => $user->icon,
                    "roomId" => $device->room_id, // [可选] 房间唯一id
                    "dupId" => $device->room->dup_id, // [可选] 房间对于dupId
                    "judge" => $device->seat_num == 0 ? 1 : 0, // [可选] 是否是法官，0否 1是
                    "seatIdx" => $device->seat_num, // [可选] 座位号，法官为0，其他从1开始
                ]));
            } else {
                return $this->json([
                    'errorMessage' => '渠道' . $channelId . '不存在！',
                    'code' => ErrorCode::CHANNEL_NONENTITY,
                ]);
            }

        }
        $token = Str::random(16);
        Cache::put($token, $user, 10);
        return $this->json([
            'errorMessage' => 'success',
            'token' => $token,
            'code' => ErrorCode::SUCCESS,
        ]);

    }

    public function info()
    {
        $token = $this->request->header('token');
        $user = Cache::get($token);
        if ($user) {
            return $this->json([
                'errorMessage' => 'success',
                'nickname' => $user->nickname,
                'sex' => $user->sex,
                'icon' => $user->icon,
                'code' => ErrorCode::SUCCESS,
            ]);
        } else {
            return $this->json([
                'errorMessage' => '你未登录',
                'code' => ErrorCode::ACCOUNT_NOT_LOGIN,
            ]);
        }
    }
}
