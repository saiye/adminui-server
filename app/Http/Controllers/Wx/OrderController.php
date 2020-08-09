<?php

namespace App\Http\Controllers\Wx;

use App\Constants\ErrorCode;
use App\Models\GoodsSku;
use App\Models\Order;

class OrderController extends Base
{

    /**
     * 订单预览
     */
    public function preview()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'buys' => 'required|json',
            'user_coupon_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $buys=json_decode($this->request->input('buys'),true);
        foreach ($buys as $buy){
            $validator2 = $this->validationFactory->make($buy, [
                'goodsId' => 'required|numeric',
                'skuIds' => 'required|array',
                'type' => 'required|numeric|in:1',
                'count' => 'required|numeric|min:1',
            ]);
            if ($validator2->fails()) {
                return $this->json([
                    'errorMessage' => $validator2->errors()->first(),
                    'code' => ErrorCode::VALID_FAILURE,
                ]);
            }
        }
        //计算金额

    }


    /**
     * 下单接口
     */
    public function createOrder()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'buys' => 'required|json',
            'user_coupon_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $buys=json_decode($this->request->input('buys'),true);
        $goodsIds=[];
        foreach ($buys as $buy){
            $validator2 = $this->validationFactory->make($buy, [
                'goodsId' => 'required|numeric',
                'skuIds' => 'required|array',
                'type' => 'required|numeric|in:1',
                'count' => 'required|numeric|min:1',
            ]);
            if ($validator2->fails()) {
                return $this->json([
                    'errorMessage' => $validator2->errors()->first(),
                    'code' => ErrorCode::VALID_FAILURE,
                ]);
            }
            array_push($goodsIds,$buy['goodsId']);
        }
        /**
         * 跨门店订单处理
         */
        $skuList=Goods::select('goods_name','goods_id','store_id','goods_sku.stock','goods_sku.goods_price','goods_sku.sku_id','goods_sku.tag_id')->leftJoin('goods_sku','goods.goods_id','=','goods_sku.goods_id')->whereIn('goods.goods_id',$goodsIds)->where('goods_sku.is_del',0)->get();
        $goodsData=[];
        foreach ($skuList as $sku){
            if(!isset($goodsData[$sku['store_id']])){
                $goodsData[$sku['store_id']]=[];
            }
            array_push($goodsData[$sku['store_id']],$sku);
        }
    }

    /**
     * 发起支付
     */
    public function doPay()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'order_id' => 'required|numeric',
            'pay_type' => 'required|numeric|in,1,2',
        ],[
            'pay_type.in'=>'该支付方式不支持！',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        //
        $orderId=$this->req->input('order_id');
        $order=Order::whereOrderId($orderId)->first();
        if(!$order){
            return $this->json([
                'errorMessage' => '订单不存在！',
                'code' => ErrorCode::DATA_NULL,
            ]);
        }
        if($order->play_status==1){
            return $this->json([
                'errorMessage' => '订单已经支付成功，请勿重复操作！',
                'code' => ErrorCode::DATA_NULL,
            ]);
        }
    }
}
