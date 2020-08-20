<?php

namespace App\Http\Controllers\Client;

use App\Constants\CacheKey;
use App\Models\Certificate;
use App\Models\Channel;
use App\Models\PlayerCountRecord;
use App\Models\PlayerGameLog;
use App\Models\User;
use App\Modesl\Device;
use App\Service\GameApi\LrsApi;
use App\Service\SmsApi\HandelSms;
use Hyperf\Guzzle\CoroutineHandler;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class GameController extends Base
{

}
