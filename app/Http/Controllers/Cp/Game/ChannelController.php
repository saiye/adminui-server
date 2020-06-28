<?php

namespace App\Http\Controllers\Cp\Game;

use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Channel;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class ChannelController extends Controller
{
    public function channelList()
    {
        $data = new Channel();
        if ($this->req->channel_name) {
            $data = $data->where('channel_name', 'like', '%' . $this->req->channel_name . '%');
        }
        if ($this->req->channel_id) {
            $data = $data->whereChannelId($this->req->channel_id);
        }
        $data = $data->orderBy('channel_id', 'desc')->paginate($this->req->input('limit', 15))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function addChannel()
    {

        $validator = Validator::make($this->req->all(), [
            'channel_name' => 'required',
            'gameSrvAddr' => 'required|url',
            'loginCallBackAddr' => 'required|url',
        ], [
            'channel_name.required' => '渠道名称不能为空',
            'gameSerAddr.required' => '游戏服地址不能为空',
            'gameSerAddr.url' => '游戏服地址，不是url',
            'loginCallBackAddr.required' => '登录回调地址不能为空',
            'loginCallBackAddr.url' => '登录回调地址，不是url',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误!',10001, $validator->errors()->toArray());
        }
        $data = $this->req->only('channel_name', 'gameSrvAddr', 'loginCallBackAddr');
        $res = Channel::create($data);
        if ($res) {
            return $this->successJson('添加成功');
        }
        return $this->errorJson('添加失败!');
    }

    public function editChannel()
    {
        $validator = Validator::make($this->req->all(), [
            'channel_id' => 'required',
            'channel_name' => 'required',
            'gameSerAddr' => 'required|url',
            'loginCallBackAddr' => 'required|url',
        ], [
            'channel_id.required' => '渠道id不能为空',
            'channel_name.required' => '渠道名称不能为空',
            'gameSerAddr.required' => '游戏服地址不能为空',
            'gameSerAddr.url' => '游戏服地址，不是url',
            'loginCallBackAddr.required' => '登录回调地址不能为空',
            'loginCallBackAddr.url' => '登录回调地址，不是url',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误!',10001, $validator->errors()->toArray());
        }
        $data = $this->req->only('channel_name', 'gameSerAddr', 'loginCallBackAddr');
        $res = Channel::whereChannelId($this->req->channel_id)->update($data);
        if ($res) {
            return $this->successJson('修改成功');
        }
        return $this->errorJson('修改失败!');
    }
}

