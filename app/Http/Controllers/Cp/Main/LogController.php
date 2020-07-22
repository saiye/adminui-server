<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Http\Controllers\Cp\Main;

use App\Constants\CacheKey;
use App\Models\ActionLog;
use App\Models\ApiActionLog;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use  App\Http\Controllers\Cp\BaseController as Controller;
use Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class LogController extends Controller
{

    public function getError()
    {
        $file_path = $this->_get_path();
        $data = array();
        foreach (glob($file_path . DIRECTORY_SEPARATOR . '*.log') as $file) {
            array_push($data, [
                'file' => basename($file),
                'info' => file_get_contents($file),
            ]);
        }
        $total = count($data);
        $assign = compact('data', 'total');
        return $this->successJson($assign);
    }

    public function _get_path()
    {
        return base_path('storage' . DIRECTORY_SEPARATOR . 'logs');
    }

    public function showLog(Request $req)
    {
        $filename = $req->input('title');
        $file_path = $this->_get_path();
        $file = $file_path . DIRECTORY_SEPARATOR . $filename;
        if (is_file($file))
            $content = file_get_contents($file);
        else
            $content = '';
        $assign = compact('content');
        return $this->successJson($assign);
    }

    public function getActionLog()
    {
        $data = new ActionLog();
        if ($this->req->uri) {
            $data = $data->whereUri($this->req->uri);
        }
        if ($this->req->user) {
            $data = $data->whereUser($this->req->user);
        }
        if ($this->req->ip) {
            $data = $data->whereIp($this->req->ip);
        }
        $data = $data->orderBy('id', 'desc')->paginate($this->req->input('limit', 15))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function getApiLog()
    {
        $data = new ApiLog();
        if ($this->req->uri) {
            $data = $data->whereUri($this->req->uri);
        }
        if ($this->req->ip) {
            $data = $data->whereIp($this->req->ip);
        }
        if ($this->req->tag) {
            $data = $data->whereIp($this->req->tag);
        }
        $data = $data->orderBy('id', 'desc')->paginate($this->req->input('limit', 15))->appends($this->req->except('page'));
        $switch = Cache::get(CacheKey::API_LOG_RECORD);
        $assign = compact('data', 'switch');
        return $this->successJson($assign);
    }

    public function setApiLog()
    {
        $validator = Validator::make($this->req->all(), [
            'switch' => ['required', 'boolean'],
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $switch = $this->req->input('switch');
        //2小时有效记录
        Cache::put(CacheKey::API_LOG_RECORD, $switch, 7200);
        if ($switch) {
            return $this->successJson([], '已开启记录器，记录器2小时内有效!');
        }
        return $this->errorJson('已关闭记录器');
    }


}

