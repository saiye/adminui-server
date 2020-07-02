<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/6/30
 * Time: 14:41
 */

namespace App\Service\LoginApi;

use App\Constants\ErrorCode;
use App\Models\ThreeUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

abstract class BaseLoginApi
{
    public $request = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 获取api相关配置数组
     */
    public function config()
    {
        $type = $this->type();

        return Config::get('auth2.' . $type, []);
    }

    public function getUser()
    {
        list($code, $info) = $this->code2Session();
        if ($code == 0) {
            $hasThreeUser = ThreeUser::whereOpenId($info['openid'])->first();
            if (!$hasThreeUser) {
                DB::beginTransaction();
                $threeUser = null;
                $user = User::create([
                    'account' => $info['openid'],
                    'password' => Hash::make($info['openid']),
                    'sex' => $info['sex'],
                    'judge' => 2,
                    'lock' => 1,
                    'icon' => $info['icon'],
                ]);
                if ($user) {
                    $threeUser = ThreeUser::created([
                        'union_id' => $info['union_id'],
                        'open_id' => $info['openid'],
                        'icon' => $info['icon'],
                        'user_id' => $user->id,
                        'session_key' => $info['session_key'],
                    ]);
                }
                if ($user and $threeUser) {
                    DB::commit();
                    return [ErrorCode::SUCCESS, '新用户', $user];
                } else {
                    DB::rollBack();
                    return [ErrorCode::CREATE_ACCOUNT_ERROR, '创建用户失败', null];
                }
            }
            //已注册用户
            $user = User::whereId($hasThreeUser->user_id)->first();
            return [ErrorCode::SUCCESS, '老用户', $user];
        }
        return [$code, $info['message'], null];
    }


    abstract protected function refreshAccessToken();

    abstract protected function code2Session();

    abstract protected function type();

}
