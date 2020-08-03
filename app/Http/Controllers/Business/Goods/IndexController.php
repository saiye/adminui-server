<?php

namespace App\Http\Controllers\Business\Goods;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Goods;
use App\Models\GoodsImage;
use App\Models\GoodsQuickCat;
use App\Models\GoodsSku;
use App\Models\GoodsTag;
use Illuminate\Support\Facades\DB;
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
        $data = $data->whereCompanyId($this->loginUser->company_id)->with(['images' => function ($query) {
            $query->select('image', 'goods_image_id', 'goods_id')->whereIsDel(0);
        }]);
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

        $skuArr = [];
        $goodsIdArr = $data->pluck('goods_id');
        if ($goodsIdArr) {
            $skus = GoodsSku::select('goods_sku.sku_id', 'goods_sku.active', 'goods_sku.stock', 'goods_sku.sku_name', 'goods_sku.goods_price', 'goods_sku.goods_id', 'goods_sku.tag_id', 'goods_tag.tag_name')->whereIn('goods_id', $goodsIdArr)->leftJoin('goods_tag', 'goods_sku.tag_id', '=', 'goods_tag.tag_id')->where('goods_sku.is_del', 0)->get();
            foreach ($skus as $sk) {
                if (!isset($skuArr[$sk->goods_id])) {
                    $skuArr[$sk->goods_id] = [];
                }
                if (!isset($skuArr[$sk->goods_id][$sk->tag_id])) {
                    $skuArr[$sk->goods_id][$sk->tag_id] = [
                        'tag_id' => $sk->tag_id,
                        'tag_name' => $sk->tag_name,
                        'tags' => [],
                    ];
                }
                array_push($skuArr[$sk->goods_id][$sk->tag_id]['tags'], $sk);
            }
        }
        foreach ($data as &$goods) {
            $goods->goodsTags = isset($skuArr[$goods->goods_id]) ? array_values($skuArr[$goods->goods_id]) : [];
        }
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
            return $this->errorJson($validator->errors()->first());
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
        list($status, $message, $info) = GoodsQuickCat::checkConfig($config);
        if (!$status) {
            return $this->errorJson($message, 2);
        }
        //计算默认价格
        $price = $info['price'];
        $tag = implode('/', $info['defaultTagArr']);
        $data = $this->req->only('goods_name', 'info', 'category_id');
        if (!in_array($this->loginUser->role_id, [3, 4])) {
            return $this->errorJson('仅店长，或者店员可添加商品!', 2);
        }
        $data['store_id'] = $this->loginUser->store_id;
        $data['company_id'] = $this->loginUser->company_id;
        $data['goods_price'] = $price;
        $data['tag'] = $tag;
        $data['image'] = '';
        $data['status'] = 2;//默认未上架
        $data['stock'] = 1;//默认库存1个,下单不扣减库存。为以后做准备.
        DB::beginTransaction();
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
            list($saveSuk, $totalStock) = GoodsSku::addSku($goods, $config, $this->loginUser);
            if ($saveSuk) {
                //修改库存
                // $goods->stock = $totalStock;
                // $goods->save();
                DB::commit();
                return $this->successJson([], '添加成功!');
            }
            DB::rollBack();
            return $this->errorJson('规格添加失败！');
        }
        DB::rollBack();
        return $this->errorJson('商品入库失败!');
    }

    public function editGoods()
    {
        $validator = Validator::make($this->req->all(), [
            'goods_id' => 'required|numeric',
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
            return $this->errorJson($validator->errors()->first());
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
        list($status, $message, $info) = GoodsQuickCat::checkConfig($config);
        if (!$status) {
            return $this->errorJson($message, 2);
        }
        //计算默认价格
        $price = $info['price'];
        $tag = implode('/', $info['defaultTagArr']);
        $data = $this->req->only('goods_name', 'info', 'category_id');
        if (!in_array($this->loginUser->role_id, [3, 4])) {
            return $this->errorJson('仅店长，或者店员可修改商品!', 2);
        }
        $goodsId = $this->req->input('goods_id', 0);
        $data['goods_price'] = $price;
        $data['tag'] = $tag;
        $data['status'] = 2;//默认未上架
        $data['stock'] = 1;//默认库存1个,下单不扣减库存。为以后做准备.
        DB::beginTransaction();
        $goods = Goods::whereGoodsId($goodsId)->whereCompanyId($this->loginUser->company_id)->whereStoreId($this->loginUser->store_id)->first();
        if (!$goods) {
            return $this->errorJson('不存在商品,修改失败!', 2);
        }
        if ($imageIds) {
            //绑定图片
            GoodsImage::whereCompanyId($this->loginUser->company_id)->whereStoreId($this->loginUser->store_id)->whereIn('goods_image_id', $imageIds)->update([
                'goods_id' => $goodsId
            ]);
            $newImage = GoodsImage::whereGoodsId($goodsId)->orderBy('goods_image_id', 'desc')->first();
            if ($newImage) {
                $data['image'] = $newImage->image;
            }
        }
        $upGoods = Goods::whereGoodsId($goodsId)->update($data);
        GoodsSku::whereGoodsId($goods->goods_id)->update(['is_del' => 1]);
        list($saveSuk, $totalStock) = GoodsSku::addSku($goods, $config, $this->loginUser);
        if ($upGoods and $saveSuk) {
            //修改库存
            // $goods->stock = $totalStock;
            // $goods->save();
            DB::commit();
            return $this->successJson([], '修改成功!');
        }
        DB::rollBack();
        return $this->errorJson('修改失败！');
    }

    /**
     * 售罄功能,直接改库存为0,后期可能有库存功能需求
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStock()
    {
        $validator = Validator::make($this->req->all(), [
            'goods_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $isUp = Goods::whereGoodsId($this->req->goods_id)->whereCompanyId($this->loginUser->company_id)->whereStoreId($this->loginUser->store_id)->update([
            'stock' => 0,
        ]);
        if ($isUp) {
            return $this->successJson([], '操作成功');
        }
        return $this->errorJson('操作失败');
    }

    public function setStatus()
    {
        $validator = Validator::make($this->req->all(), [
            'goods_id' => 'required|numeric',
            'status' => 'required|numeric|in:1,2',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $stock = $this->req->input('status') == 1 ? 1 : 0;
        $isUp = Goods::whereGoodsId($this->req->goods_id)->whereCompanyId($this->loginUser->company_id)->whereStoreId($this->loginUser->store_id)->update([
            'status' => $this->req->status,
            'stock' => $stock,
        ]);
        if ($isUp) {
            return $this->successJson([], '操作成功');
        }
        return $this->errorJson('操作失败');
    }
}

