<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/5
 * Time: 17:20
 */

namespace App\Service\SceneAction;


use App\Constants\ErrorCode;
use Illuminate\Http\Request;

class SceneFactory
{

    //Scene
    public $data = [];
    public $request =null;
    const  type = [1, 2];

    public $action = null;

    /**
     * @param $data
     * @return SceneBase
     */
    public static function make($scene)
    {
        $data=scene_decode($scene);
        $type = isset($data['t']) ? $data['t'] : 1;
        if (in_array($type, static::type)) {
            $class = '\\App\\Service\\SceneAction\\Action' . $type;
            $obj = new $class($data);
            return $obj;
        }
        return response()->json([
            'errorMessage' => '该扫码功能未开放!',
            'code' => ErrorCode::VALID_FAILURE,
        ], 200);
    }
}
