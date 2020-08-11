<?php

namespace App\Service\Pay;

use Illuminate\Http\Request;
use Log;

abstract class PayApi implements PayContracts
{

    protected $req = null;

    protected $config = [];

    public function __construct(Request $req)
    {
        $this->req = $req;
        $this->init();
    }

    abstract protected function init();

}



