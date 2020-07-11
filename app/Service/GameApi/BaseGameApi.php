<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/7/10
 * Time: 15:22
 */

namespace App\Service\GameApi;


abstract class BaseGameApi implements GameApi
{
    public $channel = null;

    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    public function json($data, $status = 200)
    {
        return response()->json($data, $status);
    }
}
