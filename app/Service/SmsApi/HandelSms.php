<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/18
 * Time: 16:53
 */

namespace App\Service\SmsApi;

use App\Constants\ErrorCode;
use App\Jobs\SendSmsJob;
use App\Models\NoteSms;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;

class HandelSms
{
    public $validationFactory=null;

    public function __construct(Application $app)
    {
        $this->validationFactory =$app->make('validator');
    }

    public function send($type, $area_code, $phone, $array,$action)
    {
        $check=$this->phoneCheck($area_code,$phone);
        if($check['code']!==0){
            return $check;
        }
        //1.频率处理
        $frequencyKey = $area_code . '_' . $phone;
        $frequencyKeyCode = $area_code . '_code' . $phone . '_' . $type.'_'.$action;
        $canSend = Cache::get($frequencyKey);
        $count=3;
        $count=10;
        if ($canSend<$count) {
            if ($type=='code'){
                if(!isset($array['code'])){
                    return [
                        'errorMessage' => '发送验证码，必须带参数code!',
                        'code' => ErrorCode::SMS_OFTEN,
                    ];
                }
                Cache::put($frequencyKeyCode,$array['code'], 900);
            }
            //2.入库处理
            $NoteSms = NoteSms::create([
                'area_code' => $area_code,
                'phone' => $phone,
                'msg' =>$array,
                'create_time' => time(),
                'status' => 0,
                'type' => $type,
                'action' => $action,
            ]);
            $canSend+=1;

            Cache::put($frequencyKey,$canSend, 86400);
            //3.触发队列执行发送
            dispatch(new SendSmsJob($NoteSms));
            return [
                'errorMessage' => '验证码已经下发,有效期15分钟!',
                'code' => ErrorCode::SUCCESS,
            ];
        }
        return [
            'errorMessage' => '你的操作太频繁了，一天内只能发'.$count.'次!',
            'code' => ErrorCode::SMS_OFTEN,
        ];
    }

    public function checkCode($type, $area_code, $phone, $code,$action)
    {
        $frequencyKeyCode = $area_code . '_code' . $phone . '_' . $type.'_'.$action;
        $cacheCode = Cache::get($frequencyKeyCode);
        if($code == $cacheCode){
            Cache::forget($frequencyKeyCode);
            return true;
        }
        return false;
    }
    public function phoneCheck($area_code,$phone){
        $route=Config::get('phone.route');
        $res=$route[$area_code]??[];
        if(empty($res)){
            $res=[
                'pattern'=>'/^[0-9]*$/',
            ];
        }
        $validator = $this->validationFactory->make([
            'area_code'=>$area_code,
            'phone'=>$phone,
        ], [
            'area_code' => 'required|numeric|in:'.$area_code,
            'phone' => ['required','numeric','regex:'.$res['pattern']],
        ], [
            'area_code.required' => '地区码必填',
            'area_code.numeric' => '地区码是一个数字',
            'area_code.in' => '地区码不支持!',
            'phone.required' => '手机号必填',
            'phone.numeric' => '手机号必须是一串数字',
            'phone.regex' => '手机号格式不正确!',
        ]);
        if ($validator->fails()) {
            return [
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ];
        }
        return [
            'errorMessage' =>'验证ok',
            'code' => ErrorCode::SUCCESS,
        ];
    }
}
