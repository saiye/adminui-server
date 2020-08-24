<?php

namespace App\Service\LoginApi;

use App\Constants\CacheKey;
use App\Constants\ErrorCode;
use App\Models\PhysicsAddress;
use App\Models\QrCodePath;
use Illuminate\Cache\DynamoDbStore;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use function GuzzleHttp\default_user_agent;

/**
 *  小程序登录接口api
 * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/login/auth.code2Session.html
 */
class WeiXinLoginApi extends BaseLoginApi
{
    public function type()
    {
        return 'wx';
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
            Log::info('WX_ACCESS_TOKEN_KEY return');
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
        $sceneData = scene_encode([
            'd' => $data['deviceShortId'],
            'c' => $data['channelId'],
            't' => $data['type'],//1进游戏，2.成为法官
        ]);
        $post = [
            'scene' => $sceneData,
            'width' => $data['width'],
            'auto_color' => true,
            'line_color' => [
                'r' => 153,
                'g' => 120,
                'b' => 192,
            ],
           // 'page'=>'pages/index/login/login',
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
                $env=Config::get('app.env');
                $image_path = 'app/'.$env.'/qrCode/'.date('YmdHis').mt_rand(1,999).'.png';
                Storage::put($image_path, $str);
                $full_path = Storage::url($image_path);
                //二维码入库
                QrCodePath::create([
                    'device_id'=>$data['deviceShortId'],
                    'channel_id'=>$data['channelId'],
                    'width'=>$data['width'],
                    'path'=>$image_path,
                ]);
                return ['image_path' => $image_path, 'full_path' => $full_path];
            } else {
                Log::info('getQrCode:status code not 200!');
            }
        } catch (\Exception $e) {
            Log::info('getQrCode:error!');
            Log::info($e->getMessage());
        }
        return false;
    }
}
