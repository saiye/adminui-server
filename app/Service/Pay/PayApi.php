<?php

namespace App\Service\Pay;
use Log;

abstract class PayApi implements PayContracts
{

    protected $config = [];

    public function __construct()
    {
        $this->init();
    }
    abstract protected function init();
}



