<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/5
 * Time: 17:20
 */

namespace App\Service\SceneAction;

use App\Constants\ErrorCode;
use App\Models\Channel;
use App\Models\Device;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use Illuminate\Support\Facades\Auth;

/**
 *
 * 扫描登录游戏
 *
 * Class Action1
 * @package App\Service\SceneAction
 */
class ActionDefault extends SceneBase
{
    public function run()
    {
        return response()->json([
            'errorMessage' => '该扫码功能未开放!',
            'code' => ErrorCode::VALID_FAILURE,
        ], 200);
    }
}
