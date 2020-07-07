<?php

namespace App\Http\Controllers\Client;

use App\Constants\CacheKey;
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
        $key = CacheKey::CLIENT_USER_INFO . $uerId;
        $user = Cache::get($key);
        if (!$user) {
            $user = User::whereId($uerId)->first();
            if ($user) {
                Cache::put($key, $user, 60);
            }
        }
        if ($user) {
            return $this->json([
                'errorMessage' => 'success',
                "account" => $user->account,
                "userId" => $user->id,
                "name" => $user->nickname,
                "sex" => $user->sex,
                "icon" => $user->icon ?? '',
                "playCount" => 120,//总局数
                "successCount" => 110,//胜利局数
                "failureCount" => 10,//失败局数
                "mvpCount" => 100,//失败局数
                "svpCount" => 10,//失败局数
                "getPoliceShield" => 10,//得到警徽
                "upPolice" => 10,//上警次数
                'code' => ErrorCode::SUCCESS,
            ]);
        } else {
            return $this->json([
                'errorMessage' => '用户不存在',
                'code' => ErrorCode::ACCOUNT_NOT_EXIST,
            ]);
        }
    }
}
