<?php

/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/18
 * Time: 16:50
 */

namespace App\Service\SmsApi;


interface SmsInterface
{
    public function send($tmpCode,$area_code,$phone,$message,$action);
}
