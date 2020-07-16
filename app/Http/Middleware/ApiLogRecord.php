<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Events\ActionLogEvent;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Cache;
use App\Constants\CacheKey;

class ApiLogRecord
{
    public function handle($request, Closure $next, $tag = 'wx')
    {
        //记录操作日志
        $response = $next($request);
        //开关开启则记录日志,开关在后台控制
        if (Cache::get(CacheKey::API_LOG_RECORD)) {
            $params = $request->all();
            ApiLog::create([
                'date' => date('Y-m-d H:i:s'),
                'ip' => $request->ip(),
                'uri' => $request->path(),
                'params' => json_encode($params),
                'response' => $response->getContent(),
                'http_type' => $request->server('REQUEST_METHOD', ''),
                'tag' => $tag,
            ]);
        }
        return $response;
    }

}

