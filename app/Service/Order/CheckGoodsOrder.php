<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/10
 * Time: 10:24
 */

namespace App\Service\Order;


use App\Constants\ErrorCode;
use App\Models\Goods;

class CheckGoodsOrder extends CheckBase
{
    public function checkBuys($buys)
    {
        $goodsIds = [];
        foreach ($buys as $buy) {
            foreach ($buy['ext'] as $ext){
                $validator = $this->validationFactory->make($ext, [
                    'tag_id' => 'required|numeric',
                    'sku_id' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return [ErrorCode::VALID_FAILURE, 'ext:'.$validator->errors()->first(), []];
                }
            }
            array_push($goodsIds, $buy['goodsId']);
        }
        /**
         * 跨门店订单处理
         */
        $goodsList = Goods::select('goods_name', 'goods_id', 'store_id', 'stock', 'company_id', 'image','status')->whereIn('goods.goods_id', $goodsIds)->get();
        $goodsList = Goods::tagList($goodsList);
        $date = date('Y-m-d H:i:s');
        $data = [];
        foreach ($goodsList as $goods) {
            if($goods['status']==2){
                return [ErrorCode::GOODS_CLOSE, '商品【'.$goods->goods_name.'】已下架,请刷新商品列表，再下单！', [$goods->goods_id]];
            }
            if($goods['stock']<1){
                return [ErrorCode::GOODS_SELL_OUT, '商品【'.$goods->goods_name.'】已售罄！', [$goods->goods_id]];
            }
            foreach ($buys as $buy) {
                if ($buy['goodsId'] == $goods['goods_id']) {
                    $price=0;
                    $tmpTag=[];
                    foreach ($goods['goodsTags'] as $tag){
                        foreach ($buy['ext'] as $ext){
                            if($ext['tag_id']==$tag['tag_id']){
                                $hasTag=false;
                                foreach ($tag['tags'] as $st){
                                    if($ext['sku_id']==$st['sku_id']){
                                        $price+=$st['goods_price'];
                                        array_push($tmpTag,$st['sku_name']);
                                        $hasTag=true;
                                        continue;
                                    }
                                }
                                if(!$hasTag){
                                    return [ErrorCode::GOODS_SKU_EDIT, '商品['.$goods->goods_name.']规格不存在，或者已被修改，请刷新页面，重新下单！', [$goods->goods_id]];
                                }
                            }
                        }
                    }
                    if(empty($tmpTag)){
                        return [ErrorCode::GOODS_SKU_EDIT, '商品['.$goods->goods_name.']存在规格已下架,请刷新商品列表，再下单！', [$goods->goods_id]];
                    }
                    array_push($data, [
                        'info' => $goods->goods_name,
                        'store_id' => $goods->store_id,
                        'company_id' => $goods->company_id,
                        'staff_id' => 0,
                        'order_goods' => [
                            'goods_id' => $goods->goods_id,
                            'goods_num' => $buy['count'],
                            'goods_price' =>$price,
                            'type' => 1,
                            'image' => $goods->getRawOriginal('image'),
                            'tag' => implode('/',$tmpTag),
                            'goods_name' => $goods->goods_name,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ],
                    ]);
                }
            }
        }
        if ($data) {
            return [ErrorCode::SUCCESS, 'success', $data];
        }
        return [ErrorCode::GOODS_NOT_FIND, '商品不存在！', $goodsIds];
    }
}
