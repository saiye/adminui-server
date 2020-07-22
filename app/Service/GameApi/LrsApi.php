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
use App\Models\PlayerGameLog;
use App\Models\Room;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

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
        $uri = '/logic/queryGameInfo';
        $url = $this->channel->callBackServer . $uri;
        try {
            $client = new Client([
                'timeout' => 3,
            ]);
            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'verify' => false,
                'json' => [
                    'userId' => $userId,
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $json = $response->getBody()->getContents();
                $res = json_decode($json, true);
                if (isset($res['code']) and $res['code'] == 0 and !empty($res['users'])) {
                    $roomId = $res['roomId'];
                    $dupId = $res['dupId'];
                    $dupName = $res['dupName'];
                    $userIdArr = [];
                    $seatArr = [];
                    foreach ($res['users'] as $item) {
                        array_push($userIdArr, $item['userId']);
                        $seatArr[$item['userId']] = $item['seat'];
                    }
                    $status = $res['status'];//,状态 1-未登陆，2-未开始，3 游戏中
                    $room = Room::with('store')->whereRoomId($roomId)->first();
                    $list = User::select(['nickname', 'icon', 'sex', 'id'])->whereIn('id', $userIdArr)->get();
                    $item = [];
                    if (!empty($list->toArray())) {
                        $data = [];
                        $roomName = $room ? $room->store->store_name . '【房间' . $room->room_name . '】' : '【房间' . $roomId . '】';
                        foreach ($list as $v) {
                            $tmp = [
                                'nickname' => $v->nickname,//头像
                                'icon' => $v->icon,//头像
                                'sex' => $v->sex,//0男,1女
                                'dup_name' => $dupName,
                                'date' => date('m-d H:i'),
                                'score' => 0,//评分
                                'seat' => $seatArr[$v->id] ?? 0,
                                'room_game_id' => 0,
                                'dup_id' => $dupId,
                                'mvp' => 0,// 0 - ⽆， 1 mvp
                                'user_id' => $v->id,
                                'room_name' => $roomName,
                                'status' => $status,
                            ];
                            if ($v->id == $userId) {
                                $item = $tmp;
                            }
                            array_push($data, $tmp);
                        }
                        return $this->json([
                            'errorMessage' => '',
                            'code' => ErrorCode::SUCCESS,
                            'list' => [
                                'item' => $item,
                                'list' => $data,
                            ],
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            return $this->json([
                'errorMessage' => '网络异常，无法查询对战信息!',
                'code' => ErrorCode::CONNECTION_TIMEOUT,
            ]);
        }
        return $this->json([
            'errorMessage' => '查询不到对战信息!',
            'code' => ErrorCode::DATA_NULL,
        ]);
    }
}
