<?php

declare(strict_types=1);

namespace App\Http\Controllers\GameSrv;

use App\Constants\ErrorCode;
use App\Jobs\RoomGameLogJob;
use Illuminate\Support\Facades\Log;

class GameSrvController extends Base
{
    public function beginGame()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'roomId' => 'required',
            'dupId' => 'required',
            'beginTime' => 'required',
        ], [
            'roomId.required' => '房间不能为空',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $roomId = $this->request->input('roomId');
        $dupId = $this->request->input('dupId');
        $beginTime = $this->request->input('beginTime');
        $unitInfos = $this->request->input('unitInfos', []);
        foreach ($unitInfos as $unit) {
            $userId = $unit["userid"];
            $job = $unit["job"];
            // todo

        }


        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
        ]);
    }

    public function endGame()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'roomId' => 'required',
        ], [
            'roomId.required' => '房间不能为空',

        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $roomId = $this->request->input('roomId');


        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
        ]);
    }

    public function changeDup()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'roomId' => 'required',
            'dupId' => 'required',
        ], [
            'roomId.required' => '房间不能为空',
            'dupId.required' => '板子id不能为空',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $roomId = $this->request->input('roomId');
        $dupId = $this->request->input('dupId');

        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
        ]);
    }

    public function gameResLog()
    {
         $json= file_get_contents('php://input');
         Log::info($json);
         $data=[];
         if($json){
             $data=  json_decode($json,true);
             Log::info($data);
         }
         if(empty($data)){
             return $this->json([
                 'errorMessage' => 'null body',
                 'code' => ErrorCode::VALID_FAILURE,
             ]);
         }
        //初步验证
        $validator = $this->validationFactory->make($data, [
            'dupId' => 'required|numeric',
            'beginTick' => 'required|numeric',
            'gameRes' => 'required|in:0,1,2,3',
            'unitInfos' => 'required|array',
          //  'replayContentJson' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        foreach ($data['unitInfos'] as $val){
            //unitInfos验证
            $validator2 = $this->validationFactory->make($val, [
                'userId' => 'required|numeric',
                'job' => 'required|numeric',
                'seat' => 'required|numeric',
                'mvp' => 'required|numeric',
                'svp' => 'required|numeric',
                'res' => 'required|numeric',
                'score' => 'required|numeric',
                'police' => 'required|numeric',
            ]);
            if ($validator2->fails()) {
                return $this->json([
                    'errorMessage' => $validator2->errors()->first(),
                    'code' => ErrorCode::VALID_FAILURE,
                ]);
            }
        }
        //入队列
        dispatch(new RoomGameLogJob($data));
        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
        ]);
    }
}
