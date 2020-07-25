<?php

namespace App\Http\Controllers\Business\Goods;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Goods;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class IndexController extends Controller
{

    public function goodsList()
    {
        $data = new Goods();

        $data = $data->whereCompanyId($this->loginUser->company_id);
        if ($this->loginUser->store_id) {
            $data = $data->whereStoreId($this->loginUser->store_id);
        }
        if ($this->req->goods_name) {
            $data = $data->where('goods_name', 'like', '%' . $this->req->goods_name . '%');
        }
        if ($this->req->goods_info) {
            $data = $data->where('goods_info', 'like', '%' . $this->req->goods_info . '%');
        }

        $data = $data->orderBy('goods.goods_id', 'desc')->paginate($this->req->input('limit', $limit = $this->req->input('limit', PaginateSet::LIMIT)))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function addGoods()
    {
        $validator = Validator::make($this->req->all(), [
            'goods_name' => 'required|max:50',
            'goods_price' => 'required|numeric|min:0.01',
            'info' => 'required|max:80',
            'category_id' => 'required|numeric',
            'image' => 'required|max:100',
            'tag' => 'required|max:100',
            'tagArr' => 'required|array',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data = $this->req->only('goods_name', 'goods_price', 'info', 'category_id', 'image', 'tag');
        $data['store_id'] = 0;
        $data['company_id'] = 0;
        $board = Goods::create($data);

        if ($board) {
            return $this->successJson([], '添加成功');
        } else {
            return $this->errorJson('入库失败');
        }
    }

    public function editGoods()
    {
        $validator = Validator::make($this->req->all(), [
            'goods_id' => 'required|numeric',
            'goods_name' => 'required|max:50',
            'goods_price' => 'required|numeric|min:0.01',
            'info' => 'required|max:80',
            'category_id' => 'required|numeric',
            'image' => 'required|max:100',
            'tag' => 'required|max:100',
            'tagArr' => 'required|array',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data = $this->req->only('goods_name', 'goods_price', 'info', 'category_id', 'image', 'tag');
        $goodsId = $this->req->input('goods_id');
        $board = Goods::whereGoodsId($goodsId)->update($data);
        if ($board) {
            return $this->successJson([], '修改成功');
        } else {
            return $this->errorJson('修改失败');
        }
    }

}

