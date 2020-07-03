<?php

namespace App\Http\Controllers\Client;

use App\Modesl\Device;
use App\Models\User;
use GuzzleHttp\Client;
use Hyperf\Guzzle\CoroutineHandler;
use App\Constants\ErrorCode;


class UserController extends Base
{

    public function callLoginTest()
    {
        return [
            'code' => 0,
            'errorMessage' => 'callLoginTest success',
            'data' => $this->request->all(),
        ];
    }
}
