<?php

namespace App\Http\Controllers\Wx;


use App\Models\Goods;
use App\Models\GoodsCategory;
use App\Models\Store;
use App\Constants\ErrorCode;
use App\Models\StoreTag;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

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
        $store = Store::select(['store_id', 'store_name', 'open_at', 'close_at', 'address', 'logo'])->whereStoreId($storeId)->whereCheck(1)->first();
        if (!$store) {
            return $this->json(
                [
                    'errorMessage' => '该店铺不存在,或审核未通过!',
                    'code' => ErrorCode::DATA_NULL,
                ]
            );
        }
        $store->category = GoodsCategory::whereStoreId($storeId)->select(['category_id', 'category_name'])->whereIsDel(0)->get();
        $store->tags = StoreTag::whereStoreId($storeId)->select(['tag_id', 'tag_name'])->get();
        return $this->json(
            [
                'errorMessage' => '',
                'code' => ErrorCode::SUCCESS,
                'list' => $store,
            ]
        );
    }

    /**
     * 商店列表,
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeList()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'searchName' => 'nullable',
            'page' => 'required|numeric',
            'limit' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        /**
         *   5千米以内的店铺
         *   $distance =5 ;
         * ->havingRaw('distance < ' . $distance)
         */
        $limit = $this->request->input('limit', 10);
        $page = $this->request->input('page', 1);
        $searchName = $this->request->input('searchName');
        $skip = ceil($page - 1) * $limit;
        $user = $this->user();
        $lon = $user->lon;
        $lat = $user->lat;
        if($lon=='0.000000' and $lat=='0.000000'){
            return $this->json(
                [
                    'errorMessage' => '你没有定位,无法获取数据！',
                    'code' => ErrorCode::DATA_NULL,
                    'list' => [],
                ]
            );
        }
        $list = Store::select(['store_name', 'store_id', 'describe', 'address', 'close_at', 'open_at', DB::raw(" ROUND(
        6378.138 * 2 * ASIN(
            SQRT(
                POW(
                    SIN(
                        (
                           $lat* PI() / 180 - lat * PI() / 180
                        ) / 2
                    ),
                    2
                ) + COS($lat* PI() / 180) * COS(lat * PI() / 180) * POW(
                    SIN(
                        (
                            $lon * PI() / 180 - lon * PI() / 180
                        ) / 2
                    ),
                    2
                )
            )
        ),2
    ) AS distance")])->whereCheck(1)->whereIsClose(0);

        if ($searchName) {
            $list = $list->where('store_name', 'like', '%' . $searchName . '%');
        }

        $list = $list->orderBy('distance', 'asc')->skip($skip)->take($limit)->get();
        if ($list) {
            //上次购物过？
            $storeIds = $list->pluck('store_id')->toArray();
            $tradeStoreIds = Order::whereUserId($user->id)->wherePayStatus(1)->whereIn('store_id', $storeIds)->get()->pluck('store_id')->toArray();
            $data = [];
            $isEmpty = empty($tradeStoreIds);
            foreach ($list as $store) {
                array_push($data, [
                    "store_name" => $store->store_name,
                    "store_id" => $store->store_id,
                    "describe" => $store->describe,
                    "address" => $store->address,
                    "close_at" => $store->close_at,
                    "open_at" => $store->open_at,
                    "distance" => $store->distance,
                    "trade" => $isEmpty ? 0 : (in_array($store->store_id, $tradeStoreIds) ? 1 : 0),
                ]);
            }
            return $this->json(
                [
                    'errorMessage' => '',
                    'code' => ErrorCode::SUCCESS,
                    'list' => $data,
                ]
            );
        }

        return $this->json(
            [
                'errorMessage' => '你附近没发现更多店铺了!',
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
        $list = Goods::select(['goods_name', 'goods_price', 'goods_id', 'image', 'info', 'tag', 'stock'])->whereCategoryId($category_id)->whereStatus(1)->skip($skip)->take($limit)->get();
        if ($list) {
            $list = Goods::tagList($list);
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
