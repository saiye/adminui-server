<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/18
 * Time: 16:52
 */

namespace App\Service\SmsApi;

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
        $conf=Config::get('phone.vonSmsKey');
        $accessKeyId = $conf['accessKeyId'];
        $accessSecret = $conf['accessSecret'];
        $from =$conf['from'];
        $phone=$area_code.$phone;
        switch ($area_code){
            case 86:
                //86区号发送成功，收不到短信，原因不明.
                $templateCode=WebConfig::getKeyByFile('vonageSms86.'.$tmpCode);
                break;
            default:
                $templateCode=WebConfig::getKeyByFile('vonageSms852.'.$tmpCode);
        }
        if(!$templateCode){
            Log::info('VonageSendSms-templateCode:null');
        }
        $new=[];
        foreach ($TemplateParam as $k=>$v){
            $_k='${'.$k.'}';
            $new[$_k]=$v;
        }
        $text=strtr($templateCode,$new);
        try {
            $basic  = new \Nexmo\Client\Credentials\Basic($accessKeyId, $accessSecret);
            $client = new \Nexmo\Client($basic);
            $message = $client->message()->send((new \Nexmo\SMS\Message\SMS($phone, $from, $text))->toArray());
            $status= $message->getStatus();
            if($status==0){
                return [true,$message->toArray()];
            }
            Log::info('VonSmsError:');
            Log::info($message->toArray());
            return [false,$message->toArray()];
        }catch (Exception $e){
               Log::info('VonageSendSms-Exception:');
               Log::info($e->getMessage());
        }
        return [false,[]];
    }
}
