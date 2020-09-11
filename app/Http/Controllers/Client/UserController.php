<?php

namespace App\Http\Controllers\Client;


use App\Constants\Logic;
use App\Constants\SmsAction;
use App\Models\Certificate;
use App\Models\Channel;
use App\Models\PlayerCountRecord;
use App\Models\User;
use App\Modesl\Device;
use App\Service\GameApi\LrsApi;
use App\Service\SmsApi\HandelSms;
use Hyperf\Guzzle\CoroutineHandler;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserController extends Base
{
    public function info()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'userId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $uerId = $this->request->input('userId');
        $user = User::whereId($uerId)->first();
        if ($user) {
            $player = PlayerCountRecord::whereUserId($uerId)->first();
            $data = [
                'errorMessage' => 'success',
                "nickname" => $user->nickname,
                "sex" => $user->sex,
                "icon" => $user->icon ?? '',
                "bigIcon" => $user->big_icon ?? '',
                "userId" => $user->id,
                "playCount" => $player ? $player->total_game : 0,//总局数
                "successCount" => $player ? $player->win_game : 0,//胜利局数
                "failureCount" => $player ? ($player->total_game - $player->win_game) : 0,//失败局数
                "mvpCount" => $player ? $player->mvp : 0,
                "svpCount" => $player ? $player->svp : 0,
                "getPoliceShield" => $player ? $player->police : 0,//得到警徽
                "upPolice" => $player ? $player->police : 0,//上警次数
                'code' => ErrorCode::SUCCESS,
            ];
            return $this->json($data);
        }
        return $this->json([
            'errorMessage' => '用户不存在',
            'code' => ErrorCode::ACCOUNT_NOT_EXIST,
        ]);
    }

    /**
     *注册检测
     */
    public function phoneRegCheck(HandelSms $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'area_code' => 'required|numeric',
            'phone' => 'required|numeric',
            'nickname' => 'required|max:20',
            'sex' => 'required|in:0,1',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $area_code = $this->request->input('area_code');
        $phone = $this->request->input('phone');
        $user=User::wherePhone($phone)->whereAreaCode($area_code)->first();
        if ($user) {
            return $this->json([
                'errorMessage' => "用户已注册",
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $data = $this->request->only('area_code', 'phone', 'nickname', 'sex');
        $data['area_code']=$area_code;

        //1.验证用户信息,ok入库
        $certificate = Str::random(32);

        $type = 'code';
        $this->cacheStep($certificate, [
            'type' => $type,
            'action' => SmsAction::USER_REG,
            'ext' => $data,
        ]);
        //2.发送验证码，和凭证给客户端
        $res = $api->send($type, $area_code, $phone, ['code' => mt_rand(11111, 99999)], SmsAction::USER_REG);
        if ($res['code'] == 0) {
            return $this->json([
                'errorMessage' => '验证码已下发！',
                'code' => ErrorCode::SUCCESS,
                'certificate' => $certificate,
            ]);
        }
        return $this->json([
            'errorMessage' => $res['errorMessage'],
            'code' => $res['code'],
        ]);
    }

    /**
     * 缓存步骤
     */
    public function cacheStep($key, $data)
    {
        $model = Certificate::create([
            'certificate' => $key,
            'data' => $data,
        ]);
        return $model;
    }

    public function getCacheStep($key)
    {
        $mode = Certificate::whereCertificate($key)->first();
        if ($mode) {
            return $mode->data;
        }
        return [];
    }

    public function removeCacheSte($key)
    {
        return Certificate::whereCertificate($key)->delete();
    }

    /**
     * 检查验证码
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCode(HandelSms $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'phone_code' => 'required|numeric',
            'certificate' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        //凭证检查
        $certificate = $this->request->input('certificate');
        $post = $this->getCacheStep($certificate);
        if (empty($post)) {
            return $this->json([
                'errorMessage' => '凭证不存在！',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        if (!isset($post['type']) or !isset($post['ext']) or !isset($post['action'])) {
            return $this->json([
                'errorMessage' => '凭证错误！',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $data = $post['ext'];
        $token = Str::random(32);
        switch ($post['action']) {
            case SmsAction::USER_REG:
                $validator2 = $this->validationFactory->make($data, [
                    'area_code' => 'required|numeric',
                    'phone' => 'required|numeric',
                    'nickname' => 'required|max:20',
                    'sex' => 'required|in:0,1',
                ]);
                if ($validator2->fails()) {
                    return $this->json([
                        'errorMessage' => "凭证不存在!",
                        'code' => ErrorCode::VALID_FAILURE,
                    ]);
                }
                //验证码检查
                if ($api->checkCode($post['type'], $data['area_code'], $data['phone'], $this->request->phone_code, $post['action'])) {
                    //考虑到，用户变更手机的情况，账号随机串
                    $account = time().mt_rand(1111,99999);
                    $user = User::whereAreaCode($data['area_code'])->wherePhone($data['phone'])->first();
                    if ($user) {
                        return $this->json([
                            'errorMessage' => '用户已经存在,请移步到登录界面！',
                            'code' => ErrorCode::CREATE_ACCOUNT_ERROR,
                        ]);
                    }
                    //验证码ok,根据凭证执行不同的逻辑,注册，或找回密码
                    $user = User::create([
                        'phone' => $data['phone'],
                        'account' => $account,
                        'sex' => $data['sex'],
                        'nickname' => $data['nickname'],
                        'area_code' => $data['area_code'],
                        'password' => Str::random(32),
                        'type'=>Logic::USER_TYPE_PHONE,
                        'token' => $token,
                    ]);
                    if ($user) {
                        $this->removeCacheSte($certificate);
                        return $this->json([
                            'errorMessage' => '验证成功!',
                            'code' => ErrorCode::SUCCESS,
                            'token' => $token,
                        ]);
                    }
                    return $this->json([
                        'errorMessage' => '注册失败!',
                        'code' => ErrorCode::VALID_FAILURE,
                    ]);
                }
                return $this->json([
                    'errorMessage' => '验证码无效!',
                    'code' => ErrorCode::VALID_FAILURE,
                ]);
                break;
            case SmsAction::USER_FORGET_PASSWORD:
            default:
                $validator3 = $this->validationFactory->make($data, [
                    'area_code' => 'required|numeric',
                    'phone' => 'required|numeric',
                ]);
                if ($validator3->fails()) {
                    return $this->json([
                        'errorMessage' => "凭证不存在!100",
                        'code' => ErrorCode::VALID_FAILURE,
                    ]);
                }
                $user=User::whereAreaCode($data['area_code'])->wherePhone($data['phone'])->first();
                if(!$user){
                    return $this->json([
                        'errorMessage' => "用户不存在",
                        'code' => ErrorCode::ACCOUNT_NOT_EXIST,
                    ]);
                }
                if ($api->checkCode($post['type'], $data['area_code'], $data['phone'], $this->request->phone_code, $post['action'])) {
                    $user->token=$token;
                    $save=  $user->save();
                    if ($save) {
                        $this->removeCacheSte($certificate);
                        return $this->json([
                            'errorMessage' => '验证成功',
                            'code' => ErrorCode::SUCCESS,
                            'token' => $token
                        ]);
                    }
                }
                return $this->json([
                    'errorMessage' => '验证码无效!',
                    'code' => ErrorCode::SMS_CODE_FAILURE,
                ]);
        }
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
        $data = $this->request->only('phone', 'area_code');
        $user = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if (!$user) {
            return $this->json([
                'errorMessage' => "用户不存在！",
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        //1.验证用户信息,ok入库
        $certificate = Str::random(32);
        $type = 'code';
        $this->cacheStep($certificate, [
            'type' => $type,
            'action' => SmsAction::USER_FORGET_PASSWORD,
            'ext' => $data,
        ]);
        $res = $api->send($type,$area_code, $phone, ['code' => mt_rand(11111, 99999)], SmsAction::USER_FORGET_PASSWORD);
        if ($res['code'] == 0) {
            return $this->json([
                'errorMessage' => "验证码下发成功",
                'code' => ErrorCode::SUCCESS,
                'certificate' => $certificate,
            ]);
        }
        return $this->json([
            'errorMessage' => $res['errorMessage'],
            'code' => $res['code'],
        ]);
    }

    /**
     * 修改密码
     */
    public function editPassword()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'token' => 'required',
            'password' => ['required', 'min:6', 'max:18', 'regex:/^(?!^(\d+|[a-zA-Z]+|[~.!@#$%^&*?]+)$)^[\w~!@#$%\^&*.?]+$/'],
            'affirm_password' => 'required|min:6|max:18|same:password',//确认密码
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $token = $this->request->input('token');
        $password = $this->request->input('password');
        $user = User::whereToken($token)->first();
        if (!$user) {
            return $this->json([
                'errorMessage' => '用户不存在！',
                'code' => ErrorCode::ACCOUNT_NOT_EXIST,
            ]);
        }
        $password = Hash::make($password);
        $user->password = $password;
        $user->save();
        return $this->json([
            'errorMessage' => '密码修改成功！',
            'code' => ErrorCode::SUCCESS,
        ]);
    }

    /**
     * 手机登录使用!
     */
    public function phoneLogin(HandelSms $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'phone' => 'required|numeric',
            'area_code' => 'required|numeric',
            'password' => ['required', 'min:6', 'max:18', 'regex:/^(?!^(\d+|[a-zA-Z]+|[~.!@#$%^&*?]+)$)^[\w~!@#$%\^&*.?]+$/'],
            'channelId' => 'required',
            'deviceShortId' => 'required',
        ], [
            'password.required' => '密码必填!',
            'password.min' => '密码最短6位!',
            'password.max' => '密码最长18位!',
            'password.regex' => '密码必须包含字母，数字，特殊符号中的两种,6-18位',
            'phone.required' => '手机号不能为空!',
            'phone.numeric' => '手机号只能是数字!',
            'area_code.numeric' => '区号只能是数字!',
            'channelId.required' => '渠道好必须存在!',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::ACCOUNT_VALID_FAILURE,
            ]);
        }
        $area_code = $this->request->input('area_code');
        $phone = $this->request->input('phone');
        $password = $this->request->input('password');
        $deviceShortId = $this->request->input('deviceShortId');
        $channelId = $this->request->input('channelId');
        $res = $api->phoneCheck($area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->json($res);
        }
        $user = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if (!$user) {
                return $this->json([
                    'errorMessage' => '账号不存在！',
                    'code' => ErrorCode::ACCOUNT_NOT_EXIST,
                ]);
        }
        if ($user and Hash::check($password, $user->password)) {
            if ($user->lock == 2) {
                return $this->json([
                    'errorMessage' => '账号已锁定!',
                    'code' => ErrorCode::ACCOUNT_LOCK,
                ]);
            }
            $device = \App\Models\Device::whereDeviceId($deviceShortId)->first();
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
                    "icon" => empty($user->icon)?'':$user->icon,
                    "bigIcon" => $user->big_icon,
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


}
