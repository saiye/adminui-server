<?php

namespace App\Service\LoginApi;
use App\Constants\ErrorCode;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 *
 * app
 * Class WeiXinAppLoginApi
 * @package App\Service\LoginApi
 */
class WeiXinAppLoginApi extends BaseLoginApi
{
    public function refreshAccessToken()
    {
        $conf=$this->config();
        $appId=$conf['AppId'];
        $AppSecret=$conf['AppSecret'];
        $code=$this->request->input('code');
        $params=[
            'appid'=>$appId,
            'secret'=>$AppSecret,
            'code'=>$code,
            'grant_type'=>'authorization_code',
        ];
        $url='https://api.weixin.qq.com/sns/oauth2/access_token?'.http_build_query($params);
        $client = new Client([
            'timeout' => 3,
        ]);
        $response = $client->get( $url, [
            'verify' => false,
        ]);
        if ($response->getStatusCode() == 200) {
            $json=$response->getBody()->getContents();
            $res=json_decode($json,true);
            Log::info('wxAppLogin:');
            Log::info($res);
            if(isset($res['access_token'])){
                return $res;
            }
        }
        return [];
    }

    public function code2Session()
    {
       $res= $this->refreshAccessToken();
       if(!$res){
           return [ErrorCode::THREE_ACCOUNT_NOT_LOGIN, ['message' => '登录code无效！']];
       }
        $params=[
            'access_token'=>$res['access_token'],
            'openid'=>$res['openid'],
        ];
        $url='https://api.weixin.qq.com/sns/userinfo?'.http_build_query($params);
        $client = new Client([
            'timeout' => 3,
        ]);
        $response = $client->get( $url, [
            'verify' => false,
        ]);
        if ($response->getStatusCode() == 200) {
            $json=$response->getBody()->getContents();
            $info=json_decode($json,true);
            if(isset($info['openid'])){
                  return [ErrorCode::SUCCESS, [
                    'openid' => $info['openid'],
                    'unionid' => $info['unionid']??$info['openid'],
                    'session_key' => $res['access_token'],
                    'sex' =>$info['sex']==1?0:1,
                    'icon' => $info['headimgurl'],
                    'nickname' => $info['nickname'],
                ]];
            }
        }
        return [ErrorCode::THREE_ACCOUNT_NOT_LOGIN, ['message' => '无法获取用户信息！']];
    }

    public function type()
    {
        return 'wxApp';
    }

    public function getQrCode($data)
    {
        // TODO: Implement getQrCode() method.
    }
}
