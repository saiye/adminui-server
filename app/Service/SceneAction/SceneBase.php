<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/5
 * Time: 17:20
 */

namespace App\Service\SceneAction;


abstract  class SceneBase implements Scene
{
    protected $data = null;
    protected $validationFactory = null;

    public function __construct($data)
    {
        $this->data = $data;
        $this->validationFactory =app()->make('validator');
    }

    public function json($data, $status = 200)
    {
        return response()->json($data, $status);
    }

}
