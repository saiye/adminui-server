<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/18
 * Time: 16:52
 */

namespace App\Service\SmsApi;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AliYunSms implements SmsInterface
{
    public function send($type, $area_code, $phone, $message)
    {
        $conf=Config::get('deploy.aliSmsKey');
        $accessKeyId = $conf['accessKeyId'];
        $accessSecret = $conf['accessSecret'];
        $regionId = 'cn-hangzhou';
        $host = 'dysmsapi.aliyuncs.com';
        $templateCode = 'SMS_121160241';//模板
        $signName = '开放测评网';
        $OutId = 1;
        $TemplateParam = json_encode(['code' => $message]);
        $date = date('Y-m-d H:i:s');
        AlibabaCloud::accessKeyClient($accessKeyId, $accessSecret)
            ->regionId($regionId)
            ->asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host($host)
                ->options([
                    'query' => [
                        'RegionId' => $regionId,
                        'PhoneNumbers' => $phone,
                        'SignName' => $signName,
                        'TemplateCode' => $templateCode,
                        'AccessKeyId' => $accessKeyId,
                        'OutId' => $OutId,
                        'TemplateParam' => $TemplateParam
                    ],
                ])->request();
            Log::info($result->toArray());
        } catch (ClientException $e) {
            Log::info($e->getErrorMessage());
        } catch (ServerException $e) {
            Log::info($e->getErrorMessage());
        }
        return true;
    }
}
