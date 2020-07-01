<?php

namespace App\Service\LoginApi;

use App\Constants\CacheKey;
use App\Constants\ErrorCode;
use App\Models\PhysicsAddress;
use Illuminate\Cache\DynamoDbStore;
use Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use function GuzzleHttp\default_user_agent;

/**
 *  小程序登录接口api
 * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/login/auth.code2Session.html
 */
class WeiXinLoginLoginApi extends BaseLoginApi
{


    public function type()
    {
        return 'wx';
    }

    public function code2Session()
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
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
            ]);
            $response = $client->get($url, $data);
            if ($response->getStatusCode() == 200) {
                $res = json_decode($response->getBody()->getContents(), true);
                if (isset($res['errcode']) and $res['errcode'] == 0) {
                    return [ErrorCode::SUCCESS, [
                        'openid' => $res['openid'],
                        'session_key' => $res['session_key'],
                        'unionid' => $res['unionid'],
                        'sex' => 0,
                        'icon' => '',
                    ]];
                }
                Log::info('刷新小程序refreshAccessToken:res');
                Log::info($res);
            }
        } catch (\Exception $e) {
            Log::info('刷新小程序refreshAccessToken:error');
            Log::info($e->getMessage());
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
            Log::info('WX_ACCESS_TOKEN_KEY return');
            return $cacheToken;
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
            ]);
            $response = $client->get($url, []);
            if ($response->getStatusCode() == 200) {
                $res = json_decode($response->getBody()->getContents(), true);
                if (isset($res['access_token'])) {
                    $access_token = $res['access_token'];
                    $expires_in = $res['expires_in'];
                    //缓存
                    Cache::put(CacheKey::WX_ACCESS_TOKEN_KEY, $access_token, $expires_in);
                    return $access_token;
                }
                Log::info('刷新小程序refreshAccessToken:res');
                Log::info($res);
            } else {
                Log::info('刷新小程序refreshAccessToken:status code not 200!');
            }
        } catch (\Exception $e) {
            Log::info('刷新小程序refreshAccessToken:error!');
            Log::info($e->getMessage());
        }
        return '';
    }

    /**
     * 获取小程序二维码
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.getUnlimited.html
     */
    public function getUnlimited($data)
    {

        // $access_token = $this->refreshAccessToken();
        $access_token = '34_ysTDn6DxUUl6KR3-JG-yLsL5PEjGCG3NoSfkzRKJoy4mvAyaRfI346Gg06MQq1_rDrEolJ39mVq4XmI0_OKwfWEzE52Q0C6UzxFc7GXbggzNNxio2_V3OUu8cwc5QXAz3sguuNY6VkIgoN7bMZIiAEAYLU';
        if (!$access_token) {
            Log::info('获取access_token失败,无法创建二维码!');
            return false;
        }
        $post = [
            'scene' => $data['scene'],
            'width' => $data['width'],
            'auto_color' => true,
            'line_color' => [
                'r' => 153,
                'g' => 120,
                'b' => 192,
            ],
            'is_hyaline' => true,
        ];
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token;
        try {
            $client = new Client([
                'timeout' => 3,
            ]);
            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'json' => $post,
            ]);
            if ($response->getStatusCode() == 200) {
                $str = $response->getBody()->getContents();

                Storage::disk('public')->put('1.png', $str);
                dd('ok');
                $res = json_decode($response->getBody()->getContents(), true);
                if (isset($res['errcode']) and $res['errcode'] == 0) {
                    $sceneData = scene_encode($data['scene']);
                    return $this->saveErCode($res['buffer'], $res['contentType'], $sceneData['deviceShortId']);
                }
                Log::info('获取小程序二维码失败：');
                Log::info($res);
            } else {
                Log::info('获取小程序二维码失败:status code not 200!');
            }
        } catch (\Exception $e) {
            Log::info('获取小程序二维码失败:error!');
            Log::info($e->getMessage());
        }
        return false;
    }

    public function httpRequest($url, $data = '', $method = 'GET')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data != '') {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;

    }

    /**
     * 保存二维码到目录
     */
    private function saveErCode($buffer, $contentType, $deviceShortId)
    {
        //保存图片到本地
        $ext = '.png';
        if (strpos($contentType, 'jpeg')) {
            $ext = '.jpg';
        }
        $fileName = 'qrCode' . DIRECTORY_SEPARATOR . $deviceShortId . $ext;

        Storage::disk('public')->put($fileName, $buffer);
        //入库
        //qrCodePath
        $device = PhysicsAddress::whereId($deviceShortId)->first();
        if (!$device->qrCodePath) {
            $device->update([
                'qrCodePath' => $fileName,
            ]);
        }

        return $fileName;

    }

}
