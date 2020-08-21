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
use App\TraitInterface\ApiTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Foundation\Application;

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
        $count=300;
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
                'msg' =>json_encode($array),
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
        return $code == $cacheCode;
    }
    public function phoneCheck($area_code,$phone){
        $route=[
            '86'=>[
                'name'=>'中国',
                'pattern'=>'/^1[345789]\d{9}$/',
            ],
            '852'=>[
                'name'=>'中国香港',
                'pattern'=>'/^[569]\d{3}\-?\d{4}$/',
            ],
            '886'=>[
                'name'=>'中国台湾',
                'pattern'=>'/^9\d{8}$/',
            ],
            '1'=>[
                'name'=>'美国',
                'pattern'=>'/^[2-9]\d{2}[2-9](?!11)\d{6}$/',
            ],
        ];
        $res=$route[$area_code]??[];
        if(empty($res)){
            return [
                'errorMessage' => '不支持该地区',
                'code' => ErrorCode::SMS_NOT_SUPPORT,
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
