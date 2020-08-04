<?php

namespace App\Http\Controllers\Business\Goods;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Goods;
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

        $data = $data->where('company_id', $this->loginUser->company_id)->whereIsDel(0);

        if ($this->loginUser->store_id) {
            $data = $data->where('store_id', $this->loginUser->store_id);
        }

        if ($this->req->cat_name) {
            $data = $data->where('category_name', 'like', '%' . $this->req->cat_name . '%');
        }

        $data = $data->orderBy('category_id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }


    public function addCat()
    {
        $validator = Validator::make($this->req->all(), [
            'category_name' => 'required|max:50',
        ],[
            'category_name.required'=>'分类不能为空',
            'category_name.max'=>'分类不能超过50字符',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        if (!$this->loginUser->store_id) {
            return $this->errorJson('仅仅店长，或者店员可以添加', 2);
        }
        $category_name = $this->req->input('category_name');
        $hasCategory = GoodsCategory::whereStoreId($this->loginUser->store_id)->where('category_name', $category_name)->whereIsDel(0)->first();
        if ($hasCategory) {
            return $this->errorJson('该分类已存在，请勿重复添加', 2);
        }
        $data = [];
        $data['store_id'] = $this->loginUser->store_id;
        $data['company_id'] = $this->loginUser->company_id;
        $data['category_name'] = $this->req->input('category_name');
        $board = GoodsCategory::create($data);
        if ($board) {
            return $this->successJson([], '添加成功');
        } else {
            return $this->errorJson('入库失败');
        }
    }

    public function editCat()
    {
        $validator = Validator::make($this->req->all(), [
            'category_name' => 'required|max:50',
            'category_id' => 'required',
        ],[
            'category_name.required'=>'分类不能为空',
            'category_name.max'=>'分类不能超过50字符',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        if (!$this->loginUser->store_id) {
            return $this->errorJson('仅店长，或者店员可以修改', 2);
        }
        $category_name = $this->req->input('category_name');
        $catId = $this->req->input('category_id');
        $hasCategory = GoodsCategory::whereStoreId($this->loginUser->store_id)->where('category_id', '!=', $catId)->where('category_name', $category_name)->whereIsDel(0)->first();
        if ($hasCategory) {
            return $this->errorJson('该分类已存在，不能添加同名分类!', 2);
        }
        $board = GoodsCategory::whereStoreId($this->loginUser->store_id)->whereCategoryId($catId)->update([
            'category_name' => $category_name,
        ]);
        if ($board) {
            return $this->successJson([], '修改成功');
        } else {
            return $this->errorJson('修改失败');
        }
    }

    /**
     * 删除分类
     */
    public function del()
    {
        $validator = Validator::make($this->req->all(), [
            'category_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        if (!$this->loginUser->store_id) {
            return $this->errorJson('仅店长，或者店员可以删除', 2);
        }
        $catId = $this->req->input('category_id');
        $hasGoods = Goods::whereStoreId($this->loginUser->store_id)->whereCategoryId($catId)->count();
        if ($hasGoods) {
            return $this->errorJson('分类下存在商品，无法删除!', 2);
        }
        $board = GoodsCategory::whereStoreId($this->loginUser->store_id)->whereCategoryId($catId)->update([
            'is_del'=>1
        ]);
        if ($board) {
            return $this->successJson([], '删除成功');
        } else {
            return $this->errorJson('删除失败');
        }
    }

    /**
     * 移动分类
     */
    public function move()
    {
        $validator = Validator::make($this->req->all(), [
            'category_id' => 'required',
            'move_cat_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        if (!$this->loginUser->store_id) {
            return $this->errorJson('仅店长，或者店员可以删除', 2);
        }
        $catId = $this->req->input('category_id');
        $move_cat_id = $this->req->input('move_cat_id');
        $hasGoods = Goods::whereStoreId($this->loginUser->store_id)->whereCategoryId($catId)->count();
        if (!$hasGoods) {
            return $this->errorJson('该分类下不存在商品，操作无效!', 2);
        }
        $hasCat = GoodsCategory::whereStoreId($this->loginUser->store_id)->whereCategoryId($catId)->first();
        $moveCat = GoodsCategory::whereStoreId($this->loginUser->store_id)->whereCategoryId($move_cat_id)->first();
        if ($hasCat and $moveCat) {
            //执行移动
            $isMove = Goods::whereStoreId($this->loginUser->store_id)->whereCategoryId($catId)->update([
                'category_id' => $move_cat_id,
            ]);
            if ($isMove) {
                $moveCat->count+=$hasCat->count;
                $moveCat->save();

                $hasCat->count=0;
                $hasCat->save();

                return $this->successJson([], '移动成功');
            }
        }
        return $this->errorJson('移动失败!');
    }

}

