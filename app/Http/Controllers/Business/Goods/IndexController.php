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

    public function storeList()
    {
        $data = new Goods();

        if ($this->req->goods_name) {
            $data = $data->where('goods_name', 'like', '%' . $this->req->goods_name . '%');
        }
        if ($this->req->goods_info) {
            $data = $data->where('goods_info', 'like', '%' . $this->req->goods_info . '%');
        }

        $data = $data->orderBy('store.store_id', 'desc')->paginate($this->req->input('limit', $limit = $this->req->input('limit', PaginateSet::LIMIT)))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function addGoods()
    {

    }

    public function editGoods()
    {

    }
}

