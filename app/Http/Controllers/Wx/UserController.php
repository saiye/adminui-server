<?php

namespace App\Http\Controllers\Wx;


use App\Constants\Logic;
use App\Constants\SmsAction;
use App\Models\Channel;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use App\Service\LoginApi\LoginApi;
use App\Constants\ErrorCode;
use App\Service\SceneAction\SceneFactory;
use App\Service\SmsApi\HandelSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                'is_build_openid' => $user->type==1?1:($user->open_id?1:0),
                'type' => $user->type,
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
            'password' => ['required', 'min:6', 'max:18', 'regex:/^(?!^(\d+|[a-zA-Z]+|[~.!@#$%^&*?]+)$)^[\w~!@#$%\^&*.?]+$/'],
        ], [
            'password.required' => '密码必填!',
            'password.min' => '密码最短6位!',
            'password.max' => '密码最长18位!',
            'area_code.numeric' => '区号只能是数字!',
            'password.regex' => '密码必须包含字母，数字，特殊符号中的两种,6-18位',
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
        $res = $api->phoneCheck($area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->json($res);
        }
        $user = $this->user();
        if ($user->phone) {
            return $this->json([
                'errorMessage' => '手机账号已绑定请勿重复操作！',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $hasUser = whereAreaCode($area_code)->wherePhone($phone)->first();
        if ($hasUser) {
            return $this->json([
                'errorMessage' => '绑定失败,手机账号已存在！',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        if (!$api->checkCode('code', $area_code, $phone, $phone_code, SmsAction::BUILD_USER_PHONE)) {
            return $this->json([
                'errorMessage' => '验证码错误',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $user->phone = $phone;
        $user->area_code = $area_code;
        $user->save();
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
        ], [
            'password.required' => '密码必填!',
            'password.min' => '密码最短6位!',
            'password.max' => '密码最长18位!',
            'password.regex' => '密码必须包含字母，数字，特殊符号中的两种,6-18位',
            'area_code.numeric' => '区号只能是数字!',
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
        $hasUser = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if ($hasUser) {
            return $this->json([
                'errorMessage' => '手机账号已存在,不能绑定！',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $data = $api->send('code', $area_code, $phone, ['code' => mt_rand(11111, 99999)], SmsAction::BUILD_USER_PHONE);
        return $this->json($data);
    }

    /**
     * 手机登录
     * @param HandelSms $api
     * @return \Illuminate\Http\JsonResponse
     */
    public function phoneLogin(HandelSms $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'phone' => 'required',
            'area_code' => 'required|numeric',
            'password' => ['required', 'min:6', 'max:18', 'regex:/^(?!^(\d+|[a-zA-Z]+|[~.!@#$%^&*?]+)$)^[\w~!@#$%\^&*.?]+$/'],
        ], [
            'phone.required' => '手机号码必填',
            'area_code.numeric' => '区号只能是数字!',
            'password.required' => '密码必填!',
            'password.min' => '密码最短6位!',
            'password.max' => '密码最长18位!',
            'password.regex' => '密码必须包含字母，数字，特殊符号中的两种,6-18位',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $area_code = $this->request->input('area_code');
        $phone = $this->request->input('phone');
        $password = $this->request->input('password');
        $res = $api->phoneCheck($area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->json($res);
        }
        $account = $area_code . $phone;

        $user = User::whereAccount($account)->first();
        if (!$user) {
            //是否存在绑定手机的小程序账号？
            $user = User::whereAreaCode($area_code)->wherePhone($phone)->first();
            if (!$user) {
                return $this->json([
                    'errorMessage' => '账号不存在！',
                    'code' => ErrorCode::ACCOUNT_NOT_EXIST,
                ]);
            }
        }
        if ($user and Hash::check($password, $user->password)) {
            $token = hash('sha256', Str::random(32));
            $user->token = $token;
            $user->type=Logic::USER_TYPE_PHONE;
            $user->save();
            return $this->json([
                'errorMessage' => 'success',
                'token' => $token,
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        return $this->json([
            'errorMessage' => '登录失败',
            'code' => ErrorCode::ACCOUNT_VALID_FAILURE,
        ]);
    }

    /**
     * 手机账号绑定openId
     */
    public function phoneAccountBuildOpenId(LoginApi $loginApi)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'js_code' => 'required',
            'longitude' => 'nullable|numeric', //可选 经度 longitude
            'latitude' => 'nullable|numeric', //可选 纬度
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        list($status, $res) = $loginApi->code2Session();
        if ($status) {
            $user = $this->user();
            $user->open_id = $res['openid'];
            $user->lon = $this->request->input('longitude', 0);
            $user->lat = $this->request->input('latitude', 0);
            $user->save();
            return $this->json([
                'errorMessage' => '绑定信息成功！',
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        return $this->json([
            'errorMessage' => '授权验证失败！',
            'code' => ErrorCode::ACCOUNT_VALID_FAILURE,
        ]);
    }

    /**
     * 修改密码
     */
    public function editPassword()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'password' => ['required', 'min:6', 'max:18', 'regex:/^(?!^(\d+|[a-zA-Z]+|[~.!@#$%^&*?]+)$)^[\w~!@#$%\^&*.?]+$/'],
            'affirm_password' => 'required|min:6|max:18|same:password',//确认密码
        ], [
            'password.required' => '密码必填!',
            'password.min' => '密码最短6位!',
            'password.max' => '密码最长18位!',
            'password.regex' => '密码必须包含字母，数字，特殊符号中的两种,6-18位',
            'affirm_password.required' => '确认密码必填!',
            'affirm_password.min' => '密码最短6位',
            'affirm_password.alpha_dash' => '验证字段可以包含字母和数字，以及破折号和下划线',
            'affirm_password.same' => '两次输入密码不一致',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $password = $this->request->post('password');
        $user = Auth::guard('wx')->user();
        $user->password = Hash::make($password);
        $user->save();
        return $this->json([
            'errorMessage' => '密码修改成功',
            'code' => ErrorCode::SUCCESS,
        ]);
    }

}
