<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/7/10
 * Time: 15:22
 */

namespace App\Service\GameApi;


use App\TraitInterface\ApiTrait;

abstract class BaseGameApi implements GameApi
{
    use ApiTrait;

    public $channel = null;

    public function __construct($channel)
    {
        $this->channel = $channel;
    }

}
