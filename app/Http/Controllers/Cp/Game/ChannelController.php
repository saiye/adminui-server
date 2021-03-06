<?php

namespace App\Http\Controllers\Cp\Game;

use App\Constants\PaginateSet;
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
        $data = $data->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function addChannel()
    {
        $validator = Validator::make($this->req->all(), [
            'channel_id' => 'required|numeric|min:1|max:300000',
            'channel_name' => 'required',
            'gameSrvAddr' => 'required',
            'callBackServer' => 'required|url',
        ], [
            'channel_id.required' => '渠道id不能为空',
            'channel_id.min' => '渠道id不能小于1',
            'channel_id.max' => '渠道id不能大于30W',
            'channel_name.required' => '渠道名称不能为空',
            'gameSrvAddr.required' => '游戏服地址不能为空',
            'callBackServer.required' => '登录回调地址不能为空',
            'callBackServer.url' => '登录回调地址，不是url',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误!',10001, $validator->errors()->toArray());
        }
        return $this->doEditOrAddChannel();
    }

    private function doEditOrAddChannel(){
        $data = $this->req->only('channel_id','channel_name', 'gameSrvAddr', 'callBackServer');
        $hasChannel= Channel::whereChannelId($this->req->channel_id)->first();
        if($hasChannel){
            $res = Channel::whereChannelId($this->req->channel_id)->update($data);
            if ($res) {
                return $this->successJson('修改成功');
            }
            return $this->errorJson('修改失败!');
        }else{
            $res = Channel::insert($data);
            if ($res) {
                return $this->successJson([],'添加成功');
            }
            return $this->errorJson('添加失败!');
        }
    }

    public function editChannel()
    {
        $validator = Validator::make($this->req->all(), [
            'channel_id' => 'required|numeric|min:1|max:300000',
            'channel_id' => 'required',
            'channel_name' => 'required',
            'gameSrvAddr' => 'required',
            'callBackServer' => 'required|url',
        ], [
            'channel_id.required' => '渠道id不能为空',
            'channel_id.min' => '渠道id不能小于1',
            'channel_id.max' => '渠道id不能大于30W',
            'channel_id.required' => '渠道id不能为空',
            'channel_name.required' => '渠道名称不能为空',
            'gameSrvAddr.required' => '游戏服地址不能为空',
            'callBackServer.required' => '登录回调地址不能为空',
            'callBackServer.url' => '登录回调地址，不是url',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误!',10001, $validator->errors()->toArray());
        }
        return $this->doEditOrAddChannel();
    }

    public function delChannel(){
        $validator = Validator::make($this->req->all(), [
            'channel_id' => 'required|numeric|min:1|max:300000',
        ], [
            'channel_id.required' => '渠道id不能为空',
            'channel_id.min' => '渠道id不能小于1',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误!',10001, $validator->errors()->toArray());
        }
        $channelId=$this->req->input('channel_id');
       $isDel= Channel::whereChannelId($channelId)->delete();
       if($isDel){
           return $this->successJson([],'删除成功');
       }
        return $this->errorJson('删除失败！');
    }
}

