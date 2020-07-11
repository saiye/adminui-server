<?php

/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/7/9
 * Time: 16:00
 */

namespace App\Service\Excel;


interface Excel
{
    public function readFile();

    public function export();

    public function import();

    public function read();
}
