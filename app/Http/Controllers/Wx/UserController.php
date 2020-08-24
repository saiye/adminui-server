<?php

namespace App\Http\Controllers\Wx;


use App\Constants\SmsAction;
use App\Models\Channel;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use App\Service\LoginApi\LoginApi;
use App\Constants\ErrorCode;
use App\Service\SceneAction\SceneFactory;
use App\Service\SmsApi\HandelSms;
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
            $user->token = hash('sha256', Str::random(60));
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
            'avatarUrl' => 'nullable|url',
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

    /**
     * 用户信息
     * @return \Illuminate\Http\JsonResponse
     */
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
                'phone' => $user->phone,
                'area_code' => $user->area_code,
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
     * 绑定手机
     */
    public function doBuildPhone(HandelSms $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'phone' => 'required|numeric',
            'area_code' => 'required|numeric',
            'phone_code' => 'required|numeric',
            'build_type' => 'required|in:1,2',
        ], [
            'phone_code.required' => '验证码不能为空！',
            'phone_code.numeric' => '验证码必须是个数字！',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $area_code = $this->request->input('area_code');
        $phone = $this->request->input('phone');
        $phone_code = $this->request->input('phone_code');
        //1.手机账号绑定到当前登录账号，2。当前登录号，绑定手机账号
        $build_type= $this->request->input('build_type');
        $res = $api->phoneCheck($area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->json($res);
        }
        $user = $this->user();
        if($user->phone or $user->parent_id){
            return $this->json([
                'errorMessage' => '手机账号已绑定请勿重复操作！',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        if ($build_type==2){
            $hasUser=whereAreaCode($area_code)->wherePhone($phone)->first();
            if(!$hasUser){
                return $this->json([
                    'errorMessage' => '绑定失败,手机账号不存在！',
                    'code' => ErrorCode::VALID_FAILURE,
                ]);
            }
        }
        if (!$api->checkCode('code', $area_code, $phone, $phone_code, SmsAction::BUILD_USER_PHONE)) {
            return $this->json([
                'errorMessage' => '验证码错误',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        if($build_type==1){
            //执行账号绑定
            User::whereAreaCode($area_code)->wherePhone($phone)->update([
                'parent_id'=>$user->id,
            ]);
            $user->phone = $phone;
            $user->area_code = $area_code;
            $user->save();
        }else{
            $user->parent_id = $hasUser->id;
            $user->save();
        }
        return $this->json([
            'errorMessage' => '绑定成功!',
            'code' => ErrorCode::SUCCESS,
        ]);
    }

    /**
     * 绑定手机获取验证码
     */
    public function buildPhoneGetCode(HandelSms $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'phone' => 'required|numeric',
            'area_code' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $area_code = $this->request->input('area_code');
        $phone = $this->request->input('phone');
        $res = $api->phoneCheck($area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->json($res);
        }
        $data = $api->send('code', $area_code, $phone, ['code'=>mt_rand(11111,99999)], SmsAction::BUILD_USER_PHONE);
        return $this->json($data);
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
