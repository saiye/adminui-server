<?php

namespace App\Http\Controllers\Business\Order;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Order;
use App\Service\Pay\HandelPay;
use Validator;
use Illuminate\Support\Facades\Config;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class IndexController extends Controller
{

    public function orderList()
    {
        $data = new Order();
        $data = $data->where('order.company_id', $this->loginUser->company_id);
        if ($this->loginUser->store_id) {
            $data = $data->where('order.store_id', $this->loginUser->store_id);
        }
        $searchName = $this->req->input('searchName');
        if ($searchName) {
            switch ($this->req->cat_id) {
                case 1:
                    //商品名称
                    $data = $data->whereIn('order_id', function ($query) use ($searchName) {
                        $query->select('order_id')->from('order_goods')->where('order_goods.goods_name', 'like', $searchName);
                    });
                    break;
                case 2:
                    //订单号
                    $data = $data->where('order.order_sn', $searchName);
                    break;
                case 3:
                    //用户名
                    $data = $data->where('order.nickname', 'like', '%' . $searchName . '%');
                    break;
                case 4:
                    //用户ID
                    $data = $data->where('order.user_id', $searchName);
                    break;
            }
        }
        $data = $data->select('order.company_id', 'order.created_at','order.due_price', 'order.actual_payment','order.is_abnormal','order.info', 'order.order_id', 'order.store_id', 'order.total_price', 'order.pay_time', 'order.coupon_price', 'order.status', 'order.pay_type', 'order.pay_status', 'order.nickname', 'order.order_sn', 'order.user_id')->with('orderGoods')->orderBy('order.order_id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 后台下单
     */
    public function createOrder()
    {
        return $this->errorJson('订单不存在');
    }

    /**
     * 退款
     */
    public function refund()
    {
        return $this->errorJson('订单不存在');
    }

    /**
     * 搜索条件
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectConfig()
    {
        $data = Config::get('pay.selectConf');

        return $this->successJson($data);
    }

    public function orderDetail()
    {
        $validator = Validator::make($this->req->all(), [
            'order_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $data = Order::whereCompanyId($this->loginUser->company_id)->whereId($this->req->order_id)->first();
        if (!$data) {
            return $this->errorJson('订单不存在');
        }
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 修改订单为完成状态
     */
    public function setOrder()
    {
        $validator = Validator::make($this->req->all(), [
            'order_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $data = Order::whereCompanyId($this->loginUser->company_id)->whereOrderId($this->req->order_id)->first();
        if (!$data) {
            return $this->errorJson('订单不存在');
        }
        if ($data->pay_status !== 1) {
            return $this->errorJson('订单未完成支付');
        }
        $data->status = 3;
        $data->save();
        return $this->successJson([], '操作成功！');
    }
    public function findOrder(HandelPay $api)
    {
        $validator = Validator::make($this->req->all(), [
            'order_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $order = Order::whereOrderId($this->req->order_id)->first();
        if($order){
           $flag= $api->make($order->pay_type)->findOrder($order);
           if($flag){
               return $this->successJson([],'订单已支付!');
           }
            return $this->errorJson('订单未支付!');
        }
        return $this->errorJson('订单不存在！');
    }
}

