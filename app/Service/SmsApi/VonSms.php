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

/**
 * https://dashboard.nexmo.com/
 * Class VonageSms
 * @package App\Service\SmsApi
 */
class VonSms implements SmsInterface
{

    public function send($tmpCode,$area_code, $phone, $TemplateParam,$action)
    {
        $conf=Config::get('deploy.vonSmsKey');
        $accessKeyId = $conf['accessKeyId'];
        $accessSecret = $conf['accessSecret'];
        $from =$conf['from'];
        $phone=$area_code.$phone;
        switch ($area_code){
            case 86:
                $templateCode=WebConfig::getKeyByFile('aliSms86.'.$tmpCode);
                break;
            default:
                $templateCode=WebConfig::getKeyByFile('aliSms852.'.$tmpCode);
        }
        try {
            $new=[];
            foreach ($TemplateParam as $k=>$v){
                $_k='${'.$k.'}';
                $new[$_k]=$v;
            }
            $basic  = new \Nexmo\Client\Credentials\Basic($accessKeyId, $accessSecret);
            $client = new \Nexmo\Client($basic);
            $message = $client->message()->send([
                'to' => $phone,
                'from' =>$from,
                'text' => strtr($templateCode,$new)
            ]);
           $status= $message->getStatus();
            if($status==0){
                return true;
            }
            Log::info('VonSmsStatus:'.$status);
        }catch (Exception $e){
               Log::info('VonageSendSms-Exception:');
               Log::info($e->getMessage());
        }
        return false;
    }
}
