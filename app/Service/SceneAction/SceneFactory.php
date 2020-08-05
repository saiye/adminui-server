<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/5
 * Time: 17:20
 */

namespace App\Service\SceneAction;


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
    public static function make($data)
    {
        $type = isset($data['type']) ? $data['type'] : 1;
        if (in_array($type, static::type)) {
            $class = '\\App\\Service\\SceneAction\\Action' . $type;
            $obj = new $class($data);
            return $obj->run();
        }
        throw new \Exception('该扫码功能未开放!');
    }
}
