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
use App\Models\WebConfig;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AliYunSms implements SmsInterface
{

    public function send($tmpCode,$area_code, $phone, $TemplateParam,$action)
    {
        $conf=Config::get('deploy.aliSmsKey');
        $accessKeyId = $conf['accessKeyId'];
        $accessSecret = $conf['accessSecret'];
        $signName =$conf['signName'];
        $regionId = 'cn-hangzhou';
        $host = 'dysmsapi.aliyuncs.com';
        switch ($area_code){
            case 86:
                $templateCode=WebConfig::getKeyByFile('sms86.'.$tmpCode);
                break;
            default:
                $phone=$area_code.$phone;
                $templateCode=WebConfig::getKeyByFile('sms852.'.$tmpCode);
        }
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
