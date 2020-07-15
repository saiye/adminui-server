<?php

namespace App\Http\Controllers\Wx;


use App\Models\Channel;
use App\Models\Device;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use App\Service\LoginApi\LoginApi;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Base
{


    public function logout(){
        $token=$this->request->header('token');
        $user=$this->user();
        if($user){
            Cache::forget($token);
            $channel=Channel::whereChannelId($user->channel_id)->first();
            $api=new LrsApi($channel);
            return  $api->logicLogout($user->id);
        }
        return $this->json([
            'errorMessage' =>'你未登录!',
            'code' => ErrorCode::VALID_FAILURE,
        ]);
    }

    /**
     * 微信小程序登录接口
     */
    public function login(LoginApi $loginApi)
    {
        Log::info($this->request->all());
        $validator = $this->validationFactory->make($this->request->all(), [
            //'scene' => 'required',
            'js_code' => 'required',
            'nickName' => 'required',
            'avatarUrl' => 'required',
            'gender' => 'required',
            'province' => 'required',
            'city' => 'required',
            'country' => 'required',
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
        $scene = $this->request->input('scene');
        if ($scene) {
            //存在则需要回调游戏登录地址
            $data = scene_decode($scene);
            $deviceShortId = (int)$data['d'] ?? 0;
            $channelId = (int)$data['c'] ?? 0;
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
                $api = new LrsApi($channel);
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
                    "deviceMqttTopic" => $device->room->deviceMqttTopic??'', // [可选]房间设备mqtt主题
                ]);
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
        $user = $this->user();
        if ($user) {
            return $this->json([
                'errorMessage' => 'success',
                'user_id' => $user->id,
                'nickname' => $user->nickname,
                'sex' => $user->sex,
                'icon' => $user->icon,
                'popularity' => $user->popularity,//人气
                'attention' => $user->attention,//关注
                'fans' => $user->fans,//粉丝
                'remaining' => $user->remaining,//余额
                'income' => $user->income,//收入
                'withdrawal' => $user->withdrawal,//已提现
                'code' => ErrorCode::SUCCESS,
            ]);
        } else {
            return $this->json([
                'errorMessage' => '你未登录',
                'code' => ErrorCode::ACCOUNT_NOT_LOGIN,
            ]);
        }
    }

    /**
     * 相册,circle
     */
    public function images()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'limit' => 'required|numeric',
            'page' => 'required|numeric',
            'userId' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
       //$list=PlayerImage::whereuserId($this->request->input('userId'))->groupBy(DB::raw(''));
        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
            'list' => [
                [
                    'month' => '5月',//月份
                    'list' => [
                        [
                            'src' => 'http://lrs-tt.7955.com/storage/qrCode/1113.png',
                            'title' => '1111',
                        ],
                        [
                            'src' => 'http://lrs-tt.7955.com/storage/qrCode/1113.png',
                            'title' => '222',
                        ]
                    ],
                ]
            ],
        ]);
    }
}
