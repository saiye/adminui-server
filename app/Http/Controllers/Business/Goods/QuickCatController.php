<?php

namespace App\Http\Controllers\Business\Goods;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\GoodsQuickCat;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class QuickCatController extends Controller
{

    public function quickCatList()
    {

        $data = new GoodsQuickCat();

        $data = $data->whereStoreId($this->loginUser->store_id);

        if ($this->req->tag_name) {
            $data = $data->where('tag_name', 'like', '%' . $this->req->tag_name . '%');
        }
        $data = $data->orderBy('id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }


    public function addQuickCat()
    {
        $validator = Validator::make($this->req->all(), [
            'tag_name' => 'required|max:30',
            'config' => 'required|array',
        ], [
            'tag_name.required' => '标签名不能为空',
            'tag_name.max' => '标签不能超过30字符',
            'config.required' => '配置不能为空',
            'config.array' => '配置只能是个数组',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $config=$this->req->input('config');
        list($status,$message)= GoodsQuickCat::checkConfig($config);
        if(!$status){
            return $this->errorJson($message, 2);
        }
        if (!$this->loginUser->store_id) {
            return $this->errorJson('仅仅店长，或者店员可以添加', 2);
        }
        $tagName = $this->req->input('tag_name');
        $hasQuick=GoodsQuickCat::whereStoreId($this->loginUser->store_id)->where('tag_name',$tagName)->first();
        if ($hasQuick) {
            return $this->errorJson('该快速标签已存在，请勿重复添加', 2);
        }
        $data =$this->req->only(['tag_name','config']);
        $data['store_id'] = $this->loginUser->store_id;
        $board = GoodsQuickCat::create($data);
        if ($board) {
            return $this->successJson([], '添加成功');
        } else {
            return $this->errorJson('入库失败');
        }
    }


}

