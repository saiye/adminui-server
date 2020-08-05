<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/5
 * Time: 17:20
 */

namespace App\Service\SceneAction;

use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Auth;

/**
 *
 * 扫描成为法官
 *
 * Class Action1
 * @package App\Service\SceneAction
 */
class Action2 extends SceneBase
{
    public function run()
    {
        $user = Auth::guard('wx')->user();
        if ($user) {
            $user->judge = 1;
            $user->save();
            return $this->json([
                'errorMessage' => '你已经成为法官',
                'code' => ErrorCode::SUCCESS,
            ]);
        }
        return $this->json([
            'errorMessage' => '你未登录',
            'code' => ErrorCode::ACCOUNT_NOT_LOGIN,
        ]);
    }
}
