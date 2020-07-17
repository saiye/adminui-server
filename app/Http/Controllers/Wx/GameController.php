<?php

namespace App\Http\Controllers\Wx;

use App\Constants\ErrorCode;
use App\Models\Channel;
use App\Models\GameBoard;
use App\Models\PlayerCountRecord;
use App\Models\PlayerGameLog;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

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
                'list' =>[
                    'total_game' => $user->total_game,
                    'win_game' => $user->win_game,
                    'mvp' => $user->mvp,
                    'svp' => $user->svp,
                    'police' => $user->police,
                ]
            ]);
        }
        return $this->json([
            'errorMessage' => '用户不存在',
            'code' => ErrorCode::ACCOUNT_NOT_EXIST,
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
        $list = PlayerGameLog::with('user')->with('board')->whereUserId($user_id);
        if ($dupId) {
            $list = $list->whereDupId($dupId);
        }
        if ($job) {
            $list = $list->whereJob($job);
        }
        if ($fightType) {
            $list = $list->whereStatus($fightType);
        }
        $skip = ceil($page - 1) * $limit;
        $list = $list->skip($skip)->take($limit)->get();
        if (!empty($list->toArray())) {
            $data = [];
            foreach ($list as $v) {
                array_push($data, [
                    'nickname' => $v->user->nickname,//头像
                    'icon' => $v->user->icon,//头像
                    'res' => $v->res,// 1- 中断， 2- 胜利 ，3- 失败
                    'dup_name' => $v->board ? $v->board->board_name : '板子' . $v->dup_id,//板子名称
                    'date' => $v->begin_tick->format('m-d H:i'),//时间
                    'score' => $v->score,//评分
                    'room_game_id' => $v->room_game_id,
                    'dup_id' => $v->dup_id,//评分
                    'mvp' => $v->mvp,// 0 - ⽆， 1 mvp
                    'sex' => $v->user->sex,//0男,1女
                    'user_id' => $v->user_id
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
        $room_game_id = $this->request->input('room_game_id',0);
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
                    'score' => $v->score,//评分
                    'room_game_id' => $v->room_game_id,
                    'dup_id' => $v->dup_id,//评分
                    'mvp' => $v->mvp,// 0 - ⽆， 1 mvp
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
        $dupList = GameBoard::select(['board_id as dup_id', 'board_name as dup_name'])->get();
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
    public function nowGame(){
        $validator = $this->validationFactory->make($this->request->all(), [
            'limit' => 'required|numeric|max:100|min:1',
            'page' => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $limit = $this->request->input('limit', 10);
        $page = $this->request->input('page', 1);
        $list = PlayerGameLog::with('user')->with('board');
        $skip = ceil($page - 1) * $limit;
        $list = $list->skip($skip)->take($limit)->get();
        if (!empty($list->toArray())) {
            $data = [];
            foreach ($list as $v) {
                array_push($data, [
                    'nickname' => $v->user->nickname,//头像
                    'icon' => $v->user->icon,//头像
                    'dup_name' => $v->board ? $v->board->board_name : '板子' . $v->dup_id,//板子名称
                    'date' => $v->begin_tick->format('m-d H:i'),//时间
                    'score' => $v->score,//评分
                    'room_game_id' => $v->room_game_id,
                    'dup_id' => $v->dup_id,//评分
                    'mvp' => $v->mvp,// 0 - ⽆， 1 mvp
                    'sex' => $v->user->sex,//0男,1女
                    'user_id' => $v->user_id
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


}
