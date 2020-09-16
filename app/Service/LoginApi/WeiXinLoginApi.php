<?php

namespace App\Service\LoginApi;

use App\Constants\CacheKey;
use App\Constants\ErrorCode;
use App\Models\QrCodePath;
use Illuminate\Support\Facades\Config;
use Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
/**
 *  小程序登录接口api
 * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/login/auth.code2Session.html
 */
class WeiXinLoginApi extends BaseLoginApi
{
    public function type()
    {
        return 'wxMin';
    }

    public function code2Session()
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session?';
        $config = $this->config();
        $data = [
            'appid' => $config['AppId'],
            'secret' => $config['AppSecret'],
            'js_code' => $this->request->input('js_code'),
            'grant_type' => 'authorization_code',
        ];
        try {
            $client = new Client([
                'timeout' => 3,
                'verify' => false,
            ]);
            $response = $client->get($url . http_build_query($data), []);
            if ($response->getStatusCode() == 200) {
                $res = json_decode($response->getBody()->getContents(), true);
                if (isset($res['openid'])) {
                    $gender = $this->request->input('gender', 0);
                    $sex=1;
                    switch ($gender) {
                        case 1:
                            $sex=0;
                            break;
                        case 0:
                        case 2:
                            $sex=1;
                            break;
                    }
                    return [ErrorCode::SUCCESS, [
                        'openid' => $res['openid'],
                        'session_key' => $res['session_key'],
                        'sex' => $sex,
                        'icon' => $this->request->input('avatarUrl', ''),
                        'nickname' => $this->request->input('nickName', ''),
                    ]];
                }
                Log::info('code2Session:res');
                Log::info($res);
            }
        } catch (\Exception $e) {
            Log::info('code2Session:error');
            Log::info($e->getMessage());
            return [ErrorCode::THREE_ACCOUNT_NOT_LOGIN, ['message' => $e->getMessage()]];
        }
        return [ErrorCode::THREE_ACCOUNT_NOT_LOGIN, ['message' => '小程序用户效验失败!']];
    }

    /**
     * 刷新accetoken
     * @return string
     *
     */
    public function refreshAccessToken()
    {
        $cacheToken = Cache::get(CacheKey::WX_ACCESS_TOKEN_KEY);
        if ($cacheToken) {
          //  Log::info('WX_ACCESS_TOKEN_KEY return');
           // return $cacheToken;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/token?';
        $config = $this->config();
        $data = [
            'grant_type' => 'client_credential',
            'appid' => $config['AppId'],
            'secret' => $config['AppSecret'],
        ];
        $url = $url . http_build_query($data);
        try {
            $client = new Client([
                'timeout' => 3,
                'verify' => false,
            ]);
            $response = $client->get($url, []);
            if ($response->getStatusCode() == 200) {
                $res = json_decode($response->getBody()->getContents(), true);
                if (isset($res['access_token'])) {
                    $access_token = $res['access_token'];
                    $expires_in = $res['expires_in']-10;
                    //缓存
                    Cache::put(CacheKey::WX_ACCESS_TOKEN_KEY, $access_token, $expires_in);
                    return $access_token;
                }
                Log::info('refreshAccessToken:res');
                Log::info($res);
            } else {
                Log::info('refreshAccessToken:status code not 200!');
            }
        } catch (\Exception $e) {
            Log::info('refreshAccessToken:error!');
            Log::info($e->getMessage());
        }
        return '';
    }

    /**
     * 获取小程序二维码
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.getQrCode.html
     */
    public function getQrCode($data)
    {
        $access_token = $this->refreshAccessToken();
        if (!$access_token) {
            Log::info('获取access_token失败,无法创建二维码!');
            return false;
        }
        $time=mt_rand(1,99);//随机数
        $hasQrCodeModel=QrCodePath::whereDeviceId($data['deviceShortId'])->whereChannelId($data['channelId'])->whereWidth($data['width'])->whereClient(0)->first();
        $deviceShortId=$data['deviceShortId'];
        $channelId=$data['channelId'];
        $width=$data['width'];
        $type=$data['type'];
        if(!$hasQrCodeModel){
            $hasQrCodeModel= QrCodePath::create([
                'device_id'=>$deviceShortId,
                'channel_id'=>$channelId,
                'width'=>$width,
                'client'=>0,
                'type'=>$type,
                'time'=>$time,
            ]);
        }
        $env=Config::get('app.env');
        //32位长度限制
        $sceneData = scene_encode([
            'id' => $hasQrCodeModel->id,
            't' => $type,
            'env'=>$env,
            'time' =>$time,
        ]);
        $post = [
            'scene' => $sceneData,
            'width' => $data['width'],
            'auto_color' => false,
            'line_color' => [
                'r' => 132,
                'g' => 98,
                'b' => 74,
            ],
            'is_hyaline'=>false,
        ];
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token;
        try {
            $client = new Client([
                'timeout' => 3,
            ]);
            $response = $client->post($url, [
                'verify' => false,
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'json' => $post,
            ]);
            if ($response->getStatusCode() == 200) {
                $str = $response->getBody()->getContents();
                $res=$this->analyJson($str);
                if(!$res){
                    $env=Config::get('app.env');
                    $image_path = 'app/'.$env.'/qrCode/'.date('YmdHis').mt_rand(1,999).'.png';
                    Storage::put($image_path, $str);
                    $full_path = Storage::url($image_path);
                    //二维码入库
                    if($hasQrCodeModel->path){
                        Storage::delete($hasQrCodeModel->path);
                    }
                    $hasQrCodeModel->path=$image_path;
                    $hasQrCodeModel->time=$time;
                    $hasQrCodeModel->type=$type;
                    $hasQrCodeModel->client=0;
                    $hasQrCodeModel->save();
                    return ['image_path' => $image_path, 'full_path' => $full_path];
                }elseif (isset($res['errmsg'])){
                    Log::info('wx erCode Cant generate');
                    Log::info($res);
                    return false;
                }
            } else {
                Log::info('getQrCode:status code not 200!');
            }
        } catch (\Exception $e) {
            Log::info('getQrCode:error!');
            Log::info($e->getMessage());
        }
        return false;
    }



    public function decryptData($sessionKey,$iv,$encryptedData){
        if (strlen($sessionKey) != 24) {
            return [false,'session_key长度错误!',[]];
        }
        $aesKey=base64_decode($sessionKey);
        $aesIV=base64_decode($iv);
        $aesCipher=$this->base64urlDecode($encryptedData);
        $result=openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj=json_decode($result);
        if($dataObj){
            $config = $this->config();
            if($dataObj->watermark->appid==$config['AppId']){
                return [true,'解密成功!',$dataObj];
            }
        }
        return [false,'解密失败!',[]];
    }

   public function base64urlDecode($data) {
        return base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
    }
}
