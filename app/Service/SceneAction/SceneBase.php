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

    public function __construct($data)
    {
        $this->data = $data;
    }

}
