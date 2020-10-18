<?php

namespace App\Http\Controllers\Business\Bench;

use  App\Http\Controllers\Business\BaseController as Controller;
use App\Service\Store\RoomService;

/**
 *
 * @author buffer
 */
class StoreBenchController extends Controller
{
    /**
     * 店铺工作台
     */
    public function indexBench(RoomService $roomService)
    {
        $storeId = $this->loginUser->room_id;
        $roomList = $roomService->storeRoomList($storeId);
        $useTotal = $roomService->storeRoomUseTotal($storeId);
        $data = compact('roomList', 'useTotal');
        return $this->successJson($data);
    }


}

