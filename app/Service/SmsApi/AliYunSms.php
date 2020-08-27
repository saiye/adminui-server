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
        //return [true,['ok'=>111,'nn'=>2]];
        $conf=Config::get('phone.aliSmsKey');
        $accessKeyId = $conf['accessKeyId'];
        $accessSecret = $conf['accessSecret'];
        $signName =$conf['signName'];
        $regionId = 'cn-hangzhou';
        $host = 'dysmsapi.aliyuncs.com';
        $phone=$area_code.$phone;
        switch ($area_code){
            case 86:
                $templateCode=WebConfig::getKeyByFile('aliSms86.'.$tmpCode);
                break;
            default:
                $templateCode=WebConfig::getKeyByFile('aliSms852.'.$tmpCode);
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
                        'TemplateParam' => json_encode($TemplateParam)
                    ],
                ])->request();
           $res= $result->toArray();
           if(isset($res['Code']) and  $res['Code']=='OK'){
               return [true,$result->toArray()];
           }
           return [false,$result->toArray()];
        } catch (ClientException $e) {
            Log::info($e->getErrorMessage());
        } catch (ServerException $e) {
            Log::info($e->getErrorMessage());
        }
        return [false,[]];
    }
}
