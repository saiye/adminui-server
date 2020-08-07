<?php

namespace App\Http\Controllers\Wx;


use App\Models\Channel;
use App\Service\GameApi\LrsApi;
use App\Service\LoginApi\LoginApi;
use App\Constants\ErrorCode;
use App\Service\SceneAction\SceneFactory;
use Illuminate\Support\Str;

class UserController extends Base
{

    public function scene()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'scene' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        //对扫码获取的参数验证合法性
        $scene = $this->request->input('scene');

        return SceneFactory::make($scene)->run();
    }

    public function logout()
    {
        $user = $this->user();
        if ($user) {
            $user->token=  hash('sha256', Str::random(60));;
            $user->save();
            $channel = Channel::whereChannelId($user->channel_id)->first();
            $api = new LrsApi($channel);
            return $api->logicLogout($user->id);
        }
        return $this->json([
            'errorMessage' => '你未登录!',
            'code' => ErrorCode::VALID_FAILURE,
        ]);
    }

    /**
     * 微信小程序登录接口
     */
    public function login(LoginApi $loginApi)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'js_code' => 'required',
            'nickName' => 'required',
            'avatarUrl' => 'required',
            'gender' => 'nullable|numeric', //可选
            'longitude' => 'nullable|numeric', //可选 经度 longitude
            'latitude' => 'nullable|numeric', //可选 纬度
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
        if (!$user) {
            return $this->json([
                'errorMessage' => '未找到用户',
                'code' => ErrorCode::ACCOUNT_NOT_EXIST,
            ]);
        }
        if ($user->lock == 2) {
            return $this->json([
                'errorMessage' => '账号已锁定!',
                'code' => ErrorCode::ACCOUNT_LOCK,
            ]);
        }
        $token = hash('sha256', Str::random(32));
        $user->token = $token;
        $user->save();
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
        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
            'list' => [
                [
                    'month' => '5月',
                    'list' => [
                        [
                            'src' => '',
                            'title' => '',
                        ],
                    ],
                ]
            ],
        ]);
    }
}
