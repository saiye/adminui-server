<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/7/10
 * Time: 15:22
 */

namespace App\Service\GameApi;


use App\Constants\ErrorCode;
use App\Jobs\CallBackGameLogin;

class LrsApi extends BaseGameApi
{


    public function loginCallBack($data)
    {
        $uri = '/loginCallback';
        $url = $this->channel->callBackServer . $uri;
        try {
            //立即调用接口队列
            dispatch_now(new CallBackGameLogin($url, $data));
        } catch (\Exception $e) {
            return $this->json([
                'errorMessage' => $e->getMessage(),
                'code' => ErrorCode::CONNECTION_TIMEOUT,
            ]);
        }
        return $this->json([
            'errorMessage' => 'success',
            'gameSrvAddr' => $this->channel->gameSrvAddr,
            'code' => ErrorCode::SUCCESS,
        ]);
    }

    public function logicLogout($userId)
    {
        $uri = '/logic/logout';
        $url = $this->channel->callBackServer . $uri;

        try {
            //立即调用接口队列
            dispatch(new CallBackGameLogin($url, [
                'userId' => $userId,
            ]));
        } catch (\Exception $e) {
            return $this->json([
                'errorMessage' => '游戏服登出失败',
                'code' => ErrorCode::CONNECTION_TIMEOUT,
            ]);
        }
        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
        ]);
    }

    public function logicQueryGameInfo($userId)
    {
        $uri = '/logic/queryGameInfo ';
        $url = $this->channel->callBackServer . $uri;
        try {
            //立即调用接口队列
            dispatch(new CallBackGameLogin($url, [
                'userId' => $userId,
            ]));
        } catch (\Exception $e) {
            return $this->json([
                'errorMessage' =>'无法查询对战信息!',
                'code' => ErrorCode::CONNECTION_TIMEOUT,
            ]);
        }
        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
        ]);
    }
}
