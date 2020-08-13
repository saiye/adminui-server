<?php

namespace App\Service\Pay;
use Illuminate\Http\Request;
use Log;

abstract class PayApi implements PayContracts
{

    protected $config = [];
    protected $request=null;

    public function __construct(Request $request)
    {
        $this->request=$request;
        $this->init();
    }
    abstract protected function init();
}



