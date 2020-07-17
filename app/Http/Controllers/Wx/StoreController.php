<?php

namespace App\Http\Controllers\Wx;


use App\Models\Goods;
use App\Models\GoodsCategory;
use App\Models\Store;
use App\Constants\ErrorCode;
use App\Models\StoreTag;

class StoreController extends Base
{

    /**
     * 店铺详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'store_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $storeId = $this->request->input('store_id');
        $store = Store::select(['store_id', 'store_name','open_at','close_at'])->whereStoreId($storeId)->whereCheck(1)->first();
        if (!$store) {
            return $this->json(
                [
                    'errorMessage' => '该店铺不存在,或审核未通过!',
                    'code' => ErrorCode::DATA_NULL,
                ]
            );
        }
        $store->category=GoodsCategory::whereStoreId($storeId)->select(['category_id','category_name'])->get();
        $store->tags=StoreTag::whereStoreId($storeId)->select(['tag_id','tag_name'])->get();
        return $this->json(
            [
                'errorMessage' => '',
                'code' => ErrorCode::SUCCESS,
                'list' => $store,
            ]
        );
    }

    /**
     * 商店列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeList()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'page' => 'required|numeric|min:1',
            'limit' => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $limit = $this->request->input('limit', 10);
        $page = $this->request->input('page', 1);
        $skip = ceil($page - 1) * $limit;
        $list = Store::select(['store_name', 'store_id','describe','close_at','open_at'])->skip($skip)->take($limit)->get();
        if ($list) {
            return $this->json(
                [
                    'errorMessage' => '',
                    'code' => ErrorCode::SUCCESS,
                    'list' => $list,
                ]
            );
        }
        return $this->json(
            [
                'errorMessage' => '没有更多的店铺了!',
                'code' => ErrorCode::DATA_NULL,
                'list' => [],
            ]
        );
    }

    /**
     * 某商店商品列表
     */
    public function goodsList()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'page' => 'required|numeric|min:1',
            'limit' => 'required|numeric|min:1',
            'category_id' => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $limit = $this->request->input('limit', 10);
        $page = $this->request->input('page', 1);
        $category_id = $this->request->input('category_id', 1);
        $skip = ceil($page - 1) * $limit;
        $list = Goods::select(['goods_name', 'goods_price', 'goods_id', 'image','info'])->whereStoreId($category_id)->whereStatus(1)->skip($skip)->take($limit)->get();
        if ($list) {
            return $this->json(
                [
                    'errorMessage' => '',
                    'code' => ErrorCode::SUCCESS,
                    'list' => $list,
                ]
            );
        }
        return $this->json(
            [
                'errorMessage' => '没有更多的商品了!',
                'code' => ErrorCode::DATA_NULL,
                'list' => [],
            ]
        );
    }


}
