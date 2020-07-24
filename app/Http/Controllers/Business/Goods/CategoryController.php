<?php

namespace App\Http\Controllers\Business\Goods;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\GoodsCategory;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class CategoryController extends Controller
{

    public function categoryList()
    {
        $data = new GoodsCategory();

        $data = $data->where('store_id', $this->loginUser->store_id);

        if ($this->req->category_name) {
            $data = $data->where('category_name', 'like', '%' . $this->req->category_name . '%');
        }

        $data = $data->orderBy('category_id', 'desc')->paginate($this->req->input('limit', $limit = $this->req->input('limit', PaginateSet::LIMIT)))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }


    public function addCategory()
    {
        $validator = Validator::make($this->req->all(), [
            'category_name' => 'required|max:50',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data = $this->req->only('category_name');
        $board = GoodsCategory::create($data);
        if ($board) {
            return $this->successJson([], '添加成功');
        } else {
            return $this->errorJson('入库失败');
        }

    }

    public function editCategory()
    {
        $validator = Validator::make($this->req->all(), [
            'category_name' => 'required|max:50',
            'category_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data = $this->req->only('category_name');
        $catId = $this->req->input('category_id');
        $board = GoodsCategory::whereCategoryId($catId)->update($data);
        if ($board) {
            return $this->successJson([], '修改成功');
        } else {
            return $this->errorJson('修改失败');
        }
    }

}

