<?php
/**
 * Created by 2020/10/18 0018 16:33
 * User: buffer
 */

namespace App\Service\Store;


use App\Constants\PaginateSet;
use App\Models\Room;
use App\Models\Store;

class RoomService
{
    /**
     * 某店面房间列表
     */
    public function storeRoomList($storeId)
    {
        return Room::whereStoreId($storeId)->get();
    }

    /**
     * 某店面房间使用情况，统计
     */
    public function storeRoomUseTotal($storeId)
    {

        //总房间
        $totalRoomCount = Room::whereStoreId($storeId)->whereOnline(1)->count();
        //空闲房间
        $emptyRoomCount = Room::whereStoreId($storeId)->whereOnline(1)->whereIsUse(0)->count();
        //使用中房间
        $useRoomCount = $totalRoomCount - $emptyRoomCount;

        //游戏中人数
        $inTheGameCount = Room::whereStoreId($storeId)->whereOnline(1)->whereIsUse(1)->sum(DB::raw("sum(seats_num-1)"));

        return [
            'inTheGameCount' => $inTheGameCount,
            'totalRoomCount' => $totalRoomCount,
            'emptyRoomCount' => $emptyRoomCount,
            'useRoomCount' => $useRoomCount,
        ];
    }

    /**
     * 某商家房间使用情况，统计
     */
    public function companyRoomUseTotal($companyId)
    {
        //总房间
        $totalRoomCount = Room::whereCompanyId($companyId)->whereOnline(1)->count();
        //空闲房间
        $emptyRoomCount = Room::whereCompanyId($companyId)->whereOnline(1)->whereIsUse(0)->count();
        //使用中房间
        $useRoomCount = $totalRoomCount - $emptyRoomCount;

        //游戏中人数
        $inTheGameCount = Room::whereCompanyId($companyId)->whereOnline(1)->whereIsUse(1)->sum(DB::raw("sum(seats_num-1)"));

        return [
            'inTheGameCount' => $inTheGameCount,
            'totalRoomCount' => $totalRoomCount,
            'emptyRoomCount' => $emptyRoomCount,
            'useRoomCount' => $useRoomCount,
        ];
    }
}
