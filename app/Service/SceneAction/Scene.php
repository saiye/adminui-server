<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/5
 * Time: 17:20
 */

namespace App\Service\SceneAction;


interface Scene
{
    public function __construct($data);

    public function run();
}
