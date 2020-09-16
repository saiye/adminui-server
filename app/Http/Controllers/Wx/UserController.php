<?php

namespace App\Http\Controllers\Wx;


use App\Constants\Logic;
use App\Constants\SmsAction;
use App\Models\Channel;
use App\Models\ThreeUser;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use App\Service\LoginApi\LoginApi;
use App\Constants\ErrorCode;
use App\Service\LoginApi\WeiXinAppLoginApi;
use App\Service\LoginApi\WeiXinLoginApi;
use App\Service\SceneAction\SceneFactory;
use App\Service\SmsApi\HandelSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Base
{

    /**
     * 小程序扫码登录游戏接口
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * 登录退出接口
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function logout()
    {
        $user = $this->user();
        if ($user) {
            $user->token = hash('sha256', Str::random(60));
            $user->save();
            $channel = Channel::whereChannelId($user->channel_id)->first();
            if($channel){
                $api = new LrsApi($channel);
                return $api->logicLogout($user->id);
            }
            return $this->json([
                'errorMessage' => trans('user.logout_successfully'),
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        return $this->json([
            'errorMessage' => trans('user.not_login'),
            'code' => ErrorCode::VALID_FAILURE,
        ]);
    }

    /**
     * android ,ios端微信登录接口
     * @param WeiXinAppLoginApi $loginApi
     * @return \Illuminate\Http\JsonResponse
     */
    public function wxAppLogin(WeiXinAppLoginApi $loginApi)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'code' => 'required',
            'longitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
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
                'errorMessage' =>trans('user.lock'),// '未找到用户',
                'code' => ErrorCode::ACCOUNT_NOT_EXIST,
            ]);
        }
        if ($user->lock == 2) {
            return $this->json([
                'errorMessage' => trans('user.lock'),
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
                'errorMessage' => trans('user.user_not_exist'),
                'code' => ErrorCode::ACCOUNT_NOT_EXIST,
            ]);
        }
        if ($user->lock == 2) {
            return $this->json([
                'errorMessage' => trans('user.lock'),
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
                'now_exp'=>0,
                'next_exp'=>3600,
                'level'=>$user->level,
                'user_id' => $user->id,
                'nickname' => $user->nickname,
                'sex' => $user->sex,
                'icon' => $user->icon,
                'bigIcon' => $user->big_icon,
                'popularity' => $user->popularity,//人气
                'attention' => $user->attention,//关注
                'fans' => $user->fans,//粉丝
                'remaining' => $user->remaining,//余额
                'income' => $user->income,//收入
                'withdrawal' => $user->withdrawal,//已提现
                'phone' => $user->phone,
                'area_code' => $user->area_code,
                'is_build_openid' => $user->type == 1 ? 1 : ($user->open_id ? 1 : 0),
                'type' => $user->type,
                'code' => ErrorCode::SUCCESS,
            ]);
        } else {
            return $this->json([
                'errorMessage' => trans('user.not_login'),
                'code' => ErrorCode::ACCOUNT_NOT_LOGIN,
            ]);
        }
    }

    /**
     * 执行绑定手机操作
     */
    public function doBuildPhone(HandelSms $api)
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
        $phone_code = $this->request->input('phone_code');
        $res = $api->phoneCheck($area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->json($res);
        }
        $user = $this->user();
        if ($user->phone == $phone) {
            return $this->json([
                'errorMessage' => trans('user.do_not_repeat_binding'),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $hasUser = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if ($hasUser) {
            return $this->json([
                'errorMessage' => trans('user.account_exist_cant_build'),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        if (!$api->checkCode('code', $area_code, $phone, $phone_code, SmsAction::BUILD_USER_PHONE)) {
            return $this->json([
                'errorMessage' => trans('user.verification_code_invalid'),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $user->phone = $phone;
        $user->area_code = $area_code;
        $user->save();
        return $this->json([
            'errorMessage' => trans('user.binding_successful'),
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
        $hasUser = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if ($hasUser) {
            return $this->json([
                'errorMessage' => trans('user.account_exist_cant_build'),
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
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $area_code = $this->request->input('area_code');
        $phone = $this->request->input('phone');
        $password = trim($this->request->input('password'));
        $res = $api->phoneCheck($area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->json($res);
        }
        $user = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if (!$user) {
            return $this->json([
                'errorMessage' => trans('user.user_not_exist'),
                'code' => ErrorCode::ACCOUNT_NOT_EXIST,
            ]);
        }
        if (Hash::check($password,$user->password)) {
            $token = hash('sha256', Str::random(32));
            $user->token = $token;
            $user->type = Logic::USER_TYPE_PHONE;
            $user->save();
            return $this->json([
                'errorMessage' =>trans('user.login_successfully'),
                'token' => $token,
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        return $this->json([
            'errorMessage' => '密码错误',
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
        $lon = $this->request->input('longitude', 0);
        $lat = $this->request->input('latitude', 0);
        $user = $this->user();
        $user->lon = $lon;
        $user->lat = $lat;
        if ($user->type !== Logic::USER_TYPE_WX) {
            //非小程序用户,绑定openid到当前账号，因为微信支付需要openid！
            list($status, $res) = $loginApi->code2Session();
            if ($status == 0) {
                $user->open_id = $res['openid'];
                $user->save();
                return $this->json([
                    'errorMessage' => trans('user.binding_successful'),
                    'code' => ErrorCode::SUCCESS,
                ]);
            }
            return $this->json([
                'errorMessage' => $res['message'],
                'code' => ErrorCode::ACCOUNT_VALID_FAILURE,
            ]);
        }
        //小程序用户操作
        $user->save();
        return $this->json([
            'errorMessage' => trans('user.binding_successful'),
            'code' => ErrorCode::SUCCESS,
        ]);
    }

    /**
     * 修改密码
     */
    public function editPassword()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'password' => ['required', 'min:6', 'max:18', 'regex:/^(?!^(\d+|[a-zA-Z]+|[~.!@#$%^&*?]+)$)^[\w~!@#$%\^&*.?]+$/'],
            'affirm_password' => 'required|min:6|max:18|same:password',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $password = $this->request->input('password');
        $user = Auth::guard('wx')->user();
        $user->password = Hash::make(trim($password));
        $editPassword=$user->save();
        if($editPassword){
            return $this->json([
                'errorMessage' => trans('user.pwd_update_success'),
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        return $this->json([
            'errorMessage' =>trans('user.pwd_update_success'),// '密码修改成功',
            'code' => ErrorCode::USER_EDIT_PWD_FAIL,
        ]);
    }

    /**
     * 小程序端，解密用户手机信息，并绑定当前登录用户
     * @param WeiXinLoginApi $api
     * @return \Illuminate\Http\JsonResponse
     */
    public function decryptData(WeiXinLoginApi $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'encryptedData' => 'required',
            'iv' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $encryptedData = $this->request->input('encryptedData');
        $iv = $this->request->input('iv');
        $user = $this->user();
        $threeUser = ThreeUser::whereUserId($user->id)->first();
        if ($threeUser) {
            list($status, $msg, $data) = $api->decryptData($threeUser->session_key, $iv, $encryptedData);
            if ($status) {
                if ($data->purePhoneNumber and $data->countryCode) {
                    if ($user->phone == $data->purePhoneNumber and $user->area_code == $data->countryCode) {
                        return $this->json([
                            'errorMessage' => '请勿重复绑定手机号！',
                            'code' => ErrorCode::REPEAT_BUILD_PHONE,
                        ]);
                    }
                    $hasOtherUser = User::whereAreaCode($data->countryCode)->wherePhone($data->purePhoneNumber)->first();
                    if ($hasOtherUser) {
                        return $this->json([
                            'errorMessage' => '该手机号码已经注册，绑定失败！',
                            'code' => ErrorCode::REPEAT_BUILD_PHONE,
                        ]);
                    }
                    $user = $this->user();
                    $user->phone = $data->purePhoneNumber;
                    $user->area_code = $data->countryCode;
                    $user->save();
                    return $this->json([
                        'errorMessage' =>trans('user.binding_successful'),// '绑定成功！',
                        'code' => ErrorCode::SUCCESS,
                    ]);
                }
            }
            return $this->json([
                'errorMessage' => $msg,
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        return $this->json([
            'errorMessage' => trans('user.user_not_exist'),
            'code' => ErrorCode::VALID_FAILURE,
        ]);
    }

    /**
     * 忘记密码,获取验证码
     */
    public function forgetPasswordSendCode(HandelSms $api)
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
        $phone = $this->request->input('phone');
        $area_code = $this->request->input('area_code');
        $res=$api->phoneCheck($area_code,$phone);
        if($res['code']!==ErrorCode::SUCCESS){
            return $this->json($res);
        }
        $user = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if (!$user) {
            return $this->json([
                'errorMessage' =>trans('user.user_not_exist'),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $type = 'code';
        $res = $api->send($type, $area_code, $phone, ['code' => mt_rand(11111, 99999)], SmsAction::USER_FORGET_PASSWORD);
        if ($res['code'] == 0) {
            return $this->json([
                'errorMessage' =>  trans('user.phone_send_code'),
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        return $this->json([
            'errorMessage' => $res['errorMessage'],
            'code' => $res['code'],
        ]);
    }

    /**
     * 对忘记密码的验证码，执行验证，成功返回token.后续用token修改密码
     * @param HandelSms $api
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgetPasswordCheckPhoneCode(HandelSms $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'phone' => 'required|numeric',
            'phone_code' => 'required|numeric',
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
        $phone_code = $this->request->input('phone_code');
        $user = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if (!$user) {
            return $this->json([
                'errorMessage' => trans('user.user_not_exist'),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        if ($api->checkCode('code', $area_code, $phone, $phone_code, SmsAction::USER_FORGET_PASSWORD)) {
            $token = Str::random(32);
            $user->token = $token;
            $save = $user->save();
            if ($save) {
                return $this->json([
                    'errorMessage' => trans('user.verify_successfully'),
                    'code' => ErrorCode::SUCCESS,
                    'token' => $token
                ]);
            }
        }
        return $this->json([
            'errorMessage' => trans('user.verification_code_invalid'),
            'code' => ErrorCode::SMS_CODE_FAILURE,
        ]);
    }

    /**
     * 手机号码注册,检测是否可以注册，可以注册则下发验证码
     */
    public function phoneRegCheckAndSendCode(HandelSms $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'area_code' => 'required',
            'phone' => 'required',
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
        $user = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if ($user) {
            return $this->json([
                'errorMessage' => trans('user.already_exist'),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        //2.发送验证码，和凭证给客户端
        $res = $api->send('code', $area_code, $phone, ['code' => mt_rand(11111, 99999)], SmsAction::USER_REG);
        if ($res['code'] == 0) {
            return $this->json([
                'errorMessage' =>trans('user.phone_send_code'),
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        return $this->json([
            'errorMessage' => $res['errorMessage'],
            'code' => $res['code'],
        ]);
    }

    /**
     * app端手机号码,进行注册
     */
    public function doPhoneReg(HandelSms $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'area_code' => 'required',
            'phone' => 'required',
            'nickname' => 'required|max:20',
            'sex' => 'required|in:0,1',
            'phone_code' => 'required|numeric',
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
        $nickname = $this->request->input('nickname');
        $sex = $this->request->input('sex');
        $res = $api->phoneCheck($area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->json($res);
        }
        $user = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if ($user) {
            return $this->json([
                'errorMessage' => trans('user.already_exist'),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }

        if ($api->checkCode('code',$area_code,$phone, $phone_code, SmsAction::USER_REG)) {
            //考虑到，用户变更手机的情况，账号随机串
            $account = time().mt_rand(1111,99999);
            $token = Str::random(32);
            $user = User::create([
                'phone' =>  $phone,
                'account' => $account,
                'sex' => $sex,
                'nickname' => $nickname,
                'area_code' =>$area_code,
                'password' =>Hash::make( Str::random(32)),
                'type'=>Logic::USER_TYPE_PHONE,
                'token' => $token,
            ]);
            if ($user) {
                return $this->json([
                    'errorMessage' => trans('user.registered_successfully'),
                    'code' => ErrorCode::SUCCESS,
                    'token' => $token,
                ]);
            }
        }
        return $this->json([
            'errorMessage' =>trans('user.verification_code_invalid'),
            'code' => ErrorCode::VALID_FAILURE,
        ]);
    }

    /**
     * 修改头像
     */
    public function updateIcon(){
        $validator = $this->validationFactory->make($this->request->all(), [
            'icon' => 'required|image',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $env=Config::get('app.env');
        $path = $this->request->file('icon')->store('app/'.$env.'/icon');
        if (!$path) {
            $this->errorJson(trans('user.upload_fail'));
        }
        $url = Storage::url($path);
        $user=$this->user();
        $oldPath=$user->getOriginal('icon');
        if($oldPath){
            Storage::delete($oldPath);
        }
        $user->icon=$path;
        $user->save();
        return $this->json([
            'errorMessage' =>trans('user.edit_success'),
            'icon' => $url,
            'code' => ErrorCode::SUCCESS,
        ]);
    }

    /**
     * 修改用户信息
     */
    public function updateUserInfo(){
        $validator = $this->validationFactory->make($this->request->all(), [
            'nickname' => 'required|max:20|min:1',
            'sex' => 'required|in:0,1',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $nickname=$this->request->input('nickname');
        $sex=$this->request->input('sex');
        $user=$this->user();
        $user->nickname=$nickname;
        $user->sex=$sex;
        $user->save();
        return $this->json([
            'errorMessage' =>trans('user.edit_success'),
            'code' => ErrorCode::SUCCESS,
        ]);
    }
}
