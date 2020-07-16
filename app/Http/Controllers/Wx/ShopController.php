<?php

namespace App\Http\Controllers\Wx;


use App\Models\Channel;
use App\Models\Device;
use App\Models\GoodsCategory;
use App\Models\Store;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use App\Service\LoginApi\LoginApi;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopController extends Base
{

    /**
     * 某商店分类
     * @return \Illuminate\Http\JsonResponse
     */
    public function category()
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
        $list = GoodsCategory::select(['category_name', 'category_id', 'store_id'])->whereStoreId($this->request->input('store_id'))->get();
        return $this->json(
            [
                'errorMessage' => '',
                'code' => ErrorCode::SUCCESS,
                'list' => $list,
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
        $list = Store::select(['store_name', 'category_id', 'store_id'])->skip($skip)->take($limit)->get();
    }

    /**
     * 某商店商品列表
     */
    public function goodsList()
    {

    }

}
