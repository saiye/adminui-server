<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/5
 * Time: 17:20
 */

namespace App\Service\SceneAction;

use App\TraitInterface\ApiTrait;

abstract  class SceneBase implements Scene
{
    use ApiTrait;
    protected $data = null;
    protected $validationFactory = null;

    public function __construct($data)
    {
        $this->data = $data;
        $this->validationFactory =app('validator');
    }
}
