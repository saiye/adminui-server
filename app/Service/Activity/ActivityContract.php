<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/7/17
 * Time: 10:18
 */

namespace App\Service\Activity;

interface  ActivityContract
{
    public function make();

    public function addActivity();

    public function editActivity();
}
