<?php

namespace App\Http\Controllers\Business\Goods;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Goods;
use App\Models\GoodsImage;
use App\Models\GoodsQuickCat;
use App\Models\GoodsSku;
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
        if ($this->req->cat_id) {
            $data = $data->whereCategoryId($this->req->cat_id);
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
            'info' => 'required|max:80',
            'category_id' => 'required|numeric',
            'quickTags' => 'required|array',
            'imageIds' => 'array',
        ], [
            'category_id.required' => '你未选择分类!',
            'goods_name.required' => '商品名称不能为空!',
            'info.required' => '商品描述不能为空!',
            'info.max' => '商品描述不能超过80字符!',
            'quickTags.required' => '商品规格参数不能为空!',
            'quickTags.array' => '商品规格必须是个数组!',
            'imageIds.array' => '商品图片数据格式错误!',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $imageIds = $this->req->input('imageIds', []);
        if ($imageIds) {
            foreach ($imageIds as $id) {
                if (!is_numeric($id) or $id < 1) {
                    return $this->errorJson('图片数据错误！', 2);
                }
            }
        }
        $config = $this->req->input('quickTags');
        list($status, $message, $data) = GoodsQuickCat::checkConfig($config);
        if (!$status) {
            return $this->errorJson($message, 2);
        }
        //计算默认价格
        $price = $data['price'];
        $tag = implode('/', $data['defaultTagArr']);
        $data = $this->req->only('goods_name', 'info', 'category_id');
        if (!in_array($this->loginUser->role_id, [3, 4])) {
            return $this->errorJson('仅店长，或者店员可添加商品!', 2);
        }
        $data['store_id'] = $this->loginUser->store_id;
        $data['company_id'] = $this->loginUser->company_id;
        $data['goods_price'] = $price;
        $data['tag'] = $tag;
        $data['image'] ='';
        $data['status'] = 2;//默认未上架
        $goods = Goods::create($data);
        if ($imageIds) {
            //绑定图片
            GoodsImage::whereCompanyId($this->loginUser->company_id)->whereStoreId($this->loginUser->store_id)->whereIn('goods_image_id', $imageIds)->update([
                'goods_id' => $goods->goods_id
            ]);
            $newImage = GoodsImage::whereCompanyId($this->loginUser->company_id)->whereStoreId($this->loginUser->store_id)->whereGoodsId($goods->goods_id)->orderBy('goods_image_id', 'desc')->first();
            if ($newImage) {
                $goods->image = $newImage->image;
                $goods->save();
            }
        }
        //添加规格数据
        if ($goods) {
            $saveSuk = GoodsSku::addSku($goods, $config, $this->loginUser);
            if ($saveSuk) {
                return $this->successJson([], '添加成功');
            }
            return $this->errorJson($message);
        }
        return $this->errorJson('商品入库失败!');
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

