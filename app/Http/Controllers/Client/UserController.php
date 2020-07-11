<?php

namespace App\Http\Controllers\Client;

use App\Constants\CacheKey;
use App\Models\PlayerCountRecord;
use App\Models\User;
use App\Modesl\Device;
use Hyperf\Guzzle\CoroutineHandler;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Cache;


class UserController extends Base
{
    public function info()
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
        $uerId = $this->request->input('userId');
        $user = User::whereId($uerId)->first();
        if ($user) {
            $player = PlayerCountRecord::whereUserId($uerId)->first();
            $data = [
                'errorMessage' => 'success',
                "nickname" => $user->nickname,
                "sex" => $user->sex,
                "icon" => $user->icon ?? '',
                "userId" => $user->id,
                "playCount" => $player ? $player->total_game : 0,//总局数
                "successCount" => $player ? $player->win_game : 0,//胜利局数
                "failureCount" => $player ? ($player->total_game - $player->win_game) : 0,//失败局数
                "mvpCount" => $player ? $player->mvp : 0,
                "svpCount" => $player ? $player->svp : 0,
                "getPoliceShield" => $player ? $player->police : 0,//得到警徽
                "upPolice" => $player ? $player->police : 0,//上警次数
                'code' => ErrorCode::SUCCESS,
            ];
            return $this->json($data);
        }
        return $this->json([
            'errorMessage' => '用户不存在',
            'code' => ErrorCode::ACCOUNT_NOT_EXIST,
        ]);
    }
}
