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

abstract class BaseLoginApi implements LoginApi
{
    protected $request = null;

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
        //经度
        $longitude =$this->request->input('longitude',0);
        //维度
        $latitude =$this->request->input('latitude',0);
        //已注册用户
        $env=Config::get('app.env');
        if($env=='local'){
            $user = User::whereId(1)->first();
            return [ErrorCode::SUCCESS, '老用户', $user];
        }
        list($code, $info) = $this->code2Session();
        if ($code == 0) {
            $hasThreeUser = ThreeUser::whereOpenId($info['openid'])->first();
            if (!$hasThreeUser) {
                DB::beginTransaction();
                $threeUser = null;
                $user = User::create([
                    'nickname' => $info['nickname'],
                    'account' => $info['openid'],
                    'password' => Hash::make($info['openid']),
                    'sex' => $info['sex'],
                    'judge' => 2,
                    'lock' => 1,
                    'icon' => $info['icon'],
                    'lon' => $longitude,
                    'lat' => $latitude,
                ]);
                if ($user) {
                    $threeUser = ThreeUser::create([
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
            if ($user) {
                //更新用户信息
                $user->sex = $info['sex'];
                $user->nickname = $info['nickname'];
                $user->icon = $info['icon'];
                $user->lon =$longitude;
                $user->lat =$latitude;
                $user->save();
                return [ErrorCode::SUCCESS, '老用户', $user];
            }

        }
        return [$code, $info['message'], null];
    }


}
