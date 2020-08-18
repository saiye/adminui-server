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

class HandelSms implements SmsInterface
{

    use ApiTrait;

    /**
     * @param $type 查看配置文件,deploy.sms_type
     * @param $area_code
     * @param $phone
     * @param $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function send($type, $area_code, $phone, $message)
    {

        //1.频率处理
        $frequencyKey = $area_code . '_' . $phone . '_' . $type;
        $canSend = Cache::get($frequencyKey);
        if (!$canSend) {
            //2.入库处理
            $NoteSms = NoteSms::create([
                'area_code' => $area_code,
                'phone' => $phone,
                'msg' => $message,
                'create_time' => time(),
                'status' => 0,
                'type' => $type,
            ]);
            Cache::put($frequencyKey, $message, 60);
            //3.触发队列执行发送
            dispatch(new SendSmsJob($NoteSms));
            return $this->json([
                'errorMessage' => '验证码已经下发!',
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        return $this->json([
            'errorMessage' => '你的操作太频繁了，1分钟后再尝试!',
            'code' => ErrorCode::SMS_OFTEN,
        ]);
    }

    public function checkCode($type, $area_code, $phone, $code)
    {
        $frequencyKey = $area_code . '_' . $phone . '_' . $type;
        $cacheCode = Cache::get($frequencyKey);
        return $code == $cacheCode;
    }
}
