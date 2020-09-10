<?php

namespace App\Http\Controllers\Wx;

use App\Constants\ErrorCode;
use App\Constants\PaginateSet;
use App\Models\Channel;
use App\Models\GameBoard;
use App\Models\PlayerCountRecord;
use App\Models\PlayerGameLog;
use App\Models\RoomGameLog;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class GameController extends Base
{
    /**
     * 玩家战绩
     */
    public function record()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'userId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $user = PlayerCountRecord::whereUserId($this->request->input('userId'))->first();
        if ($user) {
            return $this->json([
                'errorMessage' => 'success',
                'code' => ErrorCode::SUCCESS,
                'list' => [
                    'total_game' => $user->total_game,
                    'win_game' => $user->win_game,
                    'mvp' => $user->mvp,
                    'svp' => $user->svp,
                    'police' => $user->police,
                ]
            ]);
        }
        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
            'list' => [
                'total_game' => 0,
                'win_game' => 0,
                'mvp' => 0,
                'svp' => 0,
                'police' => 0,
            ]
        ]);
    }

    /**
     * 对战历史
     */
    public function fightHistorical()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'dupId' => 'required|numeric',
            'job' => 'required|numeric',
            'fightType' => 'required|numeric',
            'limit' => 'required|numeric|max:100|min:1',
            'page' => 'required|numeric|min:1',
            'userId' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $user_id = $this->request->input('userId');
        $dupId = $this->request->input('dupId');
        $job = $this->request->input('job');
        $fightType = $this->request->input('fightType');
        $limit = $this->request->input('limit', 10);
        $page = $this->request->input('page', 1);
        $list = PlayerGameLog::with('user')->with('board')->with('roomGameLog')->whereUserId($user_id);
        if ($dupId) {
            $list = $list->whereDupId($dupId);
        }
        if ($job) {
            $list = $list->whereJob($job);
        }
        if ($fightType) {
            if($fightType==4){
                $list = $list->whereMvp(1);
            }else{
                $list = $list->whereRes($fightType);
            }
        }
        $skip = ceil($page - 1) * $limit;
        $list = $list->orderBy('begin_tick','desc')->skip($skip)->take($limit)->get();
        if (!empty($list->toArray())) {
            $data = [];
            foreach ($list as $v) {
                array_push($data, [
                    'nickname' => $v->user->nickname,//头像
                    'icon' => $v->user->icon,//头像
                    'res' => $v->res,// 1- 中断， 2- 胜利 ，3- 失败
                    'dup_name' => $v->board ? $v->board->board_name : '板子' . $v->dup_id,//板子名称
                    'date' => $v->begin_tick->format('m-d H:i'),//时间
                    'score' => $v->score/10,//评分
                    'seat' => $v->seat,//位置
                    'room_game_id' => $v->room_game_id,
                    'job'=>$v->job,
                    'dup_id' => $v->dup_id,//评分
                    'mvp' => $v->mvp,// 0 - ⽆， 1 mvp
                    'svp' => $v->svp,// 0 - ⽆， 1 svp
                    'sex' => $v->user->sex,//0男,1女
                    'user_id' => $v->user_id,
                    'skinId'=>$v->roomGameLog->skinId
                ]);
            }
            return $this->json([
                'errorMessage' => '',
                'code' => ErrorCode::SUCCESS,
                'list' => $data,
            ]);
        }
        return $this->json([
            'errorMessage' => '我是有底线的!',
            'code' => ErrorCode::DATA_NULL,
            'list' => [],
        ]);
    }

    /**
     * 对局详情
     */
    public function room()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'room_game_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $room_game_id = $this->request->input('room_game_id', 0);
        $list = PlayerGameLog::with('user')->with('board')->whereRoomGameId($room_game_id)->get();
        if (!empty($list->toArray())) {
            $data = [];
            foreach ($list as $v) {
                array_push($data, [
                    'nickname' => $v->user->nickname,//头像
                    'icon' => $v->user->icon,//头像
                    'res' => $v->res,// 1- 中断， 2- 胜利 ，3- 失败
                    'dup_name' => $v->board ? $v->board->board_name : '板子' . $v->dup_id,//板子名称
                    'date' => $v->begin_tick->format('m-d H:i'),//时间
                    'score' => $v->score/10,//评分
                    'seat' => $v->seat,//位置
                    'room_game_id' => $v->room_game_id,
                    'dup_id' => $v->dup_id,//评分
                    'mvp' => $v->mvp,// 0 - ⽆， 1 mvp
                    'svp' => $v->svp,// 0 - ⽆， 1 svp
                    'user_id' => $v->user_id,
                ]);
            }
            return $this->json([
                'errorMessage' => '',
                'code' => ErrorCode::SUCCESS,
                'list' => $data,
            ]);
        }
        return $this->json([
            'errorMessage' => '我是有底线的!',
            'code' => ErrorCode::DATA_NULL,
            'list' => [],
        ]);
    }

    public function conf()
    {
        $dupList = GameBoard::select(['dup_id', 'board_name as dup_name'])->get();
        return $this->json([
            'errorMessage' => '',
            'code' => ErrorCode::SUCCESS,
            'list' => [
                'dup_list' => $dupList,
                'job_list' => array_values(Config::get('game.jobList')),
                'fight_type' => array_values(Config::get('game.fightType')),
            ],
        ]);
    }

    /**
     * 游戏中
     */
    public function nowGame()
    {
        $user = $this->user();
        if ($user) {
            if ($user->channel_id) {
                $channel = Channel::whereChannelId($user->channel_id)->first();
                if ($channel) {
                    $api = new LrsApi($channel);
                    return $api->logicQueryGameInfo($user->id);
                }
            }
            return $this->json([
                'errorMessage' => '你未登录过游戏!',
                'code' => ErrorCode::DATA_NULL,
            ]);
        }
        return $this->json([
            'errorMessage' => '你未登录!',
            'code' => ErrorCode::ACCOUNT_NOT_LOGIN,
        ]);
    }

    /*
     * 复盘
     */
    public function  roomReplay(){
        $validator = $this->validationFactory->make($this->request->all(), [
            'room_game_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $roomGameId=$this->request->input('room_game_id');
        $res=RoomGameLog::whereId($roomGameId)->first();
        if($res){
            $playerRes=PlayerGameLog::select('seat','user_id')->whereRoomGameId($roomGameId)->get()->keyBy('user_id');
            //采集
            $userIds=$playerRes->pluck('user_id');
            $userList=User::select('nickname','icon')->whereIn('id',$userIds)->get();
            $playerList=[];
            foreach ($userList as $user){
                array_push($playerList,[
                    'seat'=>isset($playerRes[$user->id])?$playerRes[$user->id]->seat:0,
                    'nickname'=>$user->nickname,
                    'icon'=>$user->icon,
                ]);
            }
            return $this->json([
                'errorMessage' => '复盘数据不存在',
                'code' => ErrorCode::SUCCESS,
                'replayData'=>$res->replayContentJson,
                'playerList'=>$playerList,
            ]);
        }
        return $this->json([
            'errorMessage' => '复盘数据不存在',
            'code' => ErrorCode::GAME_REPLAY_NULL,
        ]);
    }


    /**
     * 区号列表
     */
    public function areaCodeList(){

    }

    /**
     * 角色战绩统计
     */
    public function roleFightTotal(){
        $validator = $this->validationFactory->make($this->request->all(), [
            'userId' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $userId=$this->request->input('userId');
        $totalList = PlayerGameLog::select('job',DB::raw('sum(1) as totalNum'))->whereUserId($userId)->groupBy('job')->get();
        if(count($totalList)){
            //赢局
            $winList = PlayerGameLog::select('job',DB::raw('sum(1) as winNum'))->whereUserId($userId)->whereRes(2)->groupBy('job')->get()->keyBy('job');
            foreach ($totalList as &$v){
                $v->totalNum=$v->totalNum+0;
                $v->winNum=isset($winList[$v->job])?$winList[$v->job]->winNum+0:0;
            }
            return $this->json([
                'errorMessage' => '获取成功!',
                'code' => ErrorCode::SUCCESS,
                'data'=>$totalList,
            ]);
        }
        return $this->json([
            'errorMessage' => '数据不存在!',
            'code' => ErrorCode::DATA_NULL,
        ]);
    }

}
