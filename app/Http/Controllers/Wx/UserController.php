<?php

namespace App\Http\Controllers\Wx;

use App\Constants\CacheKey;
use App\Models\Device;
use App\Models\User;
use GuzzleHttp\Client;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;


class UserController extends Base
{
    /**
     * 微信小程序登录接口
     */
    public function login()
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
        $open_id = '';
        $user = User::whereAccount($open_id)->first();
        $token = Str::random(16);
        if (!$this->request->input('scene')) {
            Cache::put($token, $user, 10);
            return $this->json([
                'errorMessage' => 'success',
                'token' => $token,
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        //存在则login call game
        $data = json_decode(base64_decode($this->scene));
        $deviceShortId = $data['deviceShortId'] ?? 0;
        if (!is_numeric($deviceShortId) or $deviceShortId > 0) {
            return $this->json([
                'errorMessage' => 'scene值错误!',
                'code' => ErrorCode::ACCOUNT_NOT_EXIST,
            ]);
        }

        if (!$user) {
            return $this->json([
                'errorMessage' => '账号不存在',
                'code' => ErrorCode::ACCOUNT_NOT_EXIST,
            ]);
        }
        if ($user->lock == 2) {
            return $this->json([
                'errorMessage' => '账号已锁定!',
                'code' => ErrorCode::ACCOUNT_LOCK,
            ]);
        }
        $device = Device::whereDeviceId()->first();
        if (!$device) {
            return $this->json([
                'errorMessage' => '设备未绑定房间',
                'code' => ErrorCode::DEVICE_NOT_BINDING,
            ]);
        }
        if ($device->seat_num == 0 and $user->judge !== 1) {
            return $this->json([
                'errorMessage' => '该账号没有权限登录当前设备,因为普通账号,无法登陆法官设备',
                'code' => ErrorCode::ACCOUNT_NO_PREVILEGE,
            ]);
        }
        try {
            $url = Config::get('game.game_login_call_url');
            $client = new Client([
                // 'handler' => HandlerStack::create(new CoroutineHandler()),
                'timeout' => 3,
                'swoole' => [
                    'timeout' => 3,
                    'socket_buffer_size' => 1024 * 1024 * 2,
                ],
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
                Cache::put($token, $user, 10);
                return $this->json([
                    'errorMessage' => 'success',
                    'token' => $token,
                    'code' => ErrorCode::SUCCESS,
                ]);
            } else {
                return $this->json([
                    'errorMessage' => 'error',
                    'code' => ErrorCode::CONNECTION_TIMEOUT,
                ]);
            }
        } catch (\Exception $e) {
            return $this->json([
                'errorMessage' => $e->getMessage(),
                'code' => ErrorCode::SERVER_ERROR,
            ]);
        }
    }

    public function info()
    {
        $token = $this->request->header('token');
        $user = Cache::get($token);
        if ($user) {
            return $this->json([
                'errorMessage' => '',
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
