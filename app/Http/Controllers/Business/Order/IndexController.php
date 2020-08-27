<?php

namespace App\Http\Controllers\Business\Order;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Order;
use App\Models\RefundOrder;
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
        $data = $data->select('order.company_id', 'order.created_at', 'order.due_price', 'order.actual_payment', 'order.is_abnormal', 'order.info', 'order.order_id', 'order.store_id', 'order.total_price', 'order.pay_time', 'order.coupon_price', 'order.status', 'order.pay_type', 'order.pay_status', 'order.nickname', 'order.order_sn', 'order.user_id')->with('orderGoods')->orderBy('order.order_id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
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
     * 退款申请
     */
    public function refundApply()
    {
        $validator = Validator::make($this->req->all(), [
            'order_id' => 'required|numeric',
            'pay_type' => 'required|numeric',
            'refund_reason' => 'required|max:100',
            'goodsArr' => 'required|array',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $goodsArr = $this->req->input('goodsArr');
        $refund_fee = 0;
        foreach ($goodsArr as $sub) {
            $validator2 = Validator::make($sub, [
                'order_goods_id' => 'required|numeric',
                'price' => 'required|numeric',
                'count' => 'required|numeric|min:1',
            ]);
            if ($validator2->fails()) {
                return $this->errorJson($validator->errors()->first(), 2);
            }
            $refund_fee += $sub['price'];
        }
        $order_id = $this->req->input('order_id');
        $order = Order::whereCompanyId($this->loginUser->company_id)->whereOrderId($order_id)->first();
        if (!$order) {
            return $this->errorJson('订单不存在!');
        }
        if ($order->pay_status !== 1) {
            return $this->errorJson('订单未支付!');
        }
        //申请退款金额
        $pay_type = $this->req->input('pay_type');

        $refund_reason = $this->req->input('refund_reason');
        //检查是否可以退款
        //申请中的金额+已经退款金额
        $totalApplyRefundFee = RefundOrder::whereOrderId($order_id)->whereIn('check_status', [0, 1])->sum('refund_fee');
        $canApplyFee = $order->actual_payment - $totalApplyRefundFee;
        if ($canApplyFee < 0) {
            $canApplyFee = 0;
        }
        if (!$canApplyFee > 0 or $canApplyFee < $refund_fee) {
            return $this->errorJson('你无法再次申请退款，可申请退款金额为:' . $canApplyFee);
        }
        //可以退款的话，生成申请记录
        $refundOrderArr = [
            'refund_no' => date('YmdHis') . mt_rand(111, 9999),
            'order_id' => $order_id,
            'refund_fee' => $refund_fee,
            'pay_type' => $pay_type,
            'refund_reason' => $refund_reason,
            'info' => $refund_reason,
            'user_id' => $order->user_id,
            'comapny_id' => $this->loginUser->company_id,
            'store_id' => $this->loginUser->store_id,
        ];
        $refundOrder = RefundOrder::create($refundOrderArr);

        return $this->errorJson('申请成功，等待审核！');
    }

    /**
     * 退款申请列表
     */
    public function refundApplyList()
    {
        $list = RefundOrder::whereCompanyId($this->loginUser->company_id);
        if ($this->loginUser->store_id) {
            $list = $list->whereStoreId($this->loginUser->store_id);
        }
        if ($this->req->refund_no) {
            $list = $list->where('refund_no', $this->req->refund_no);
        }
        $data = $list->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 退款审核
     */
    public function refundCheck(HandelPay $api)
    {
        $validator = Validator::make($this->req->all(), [
            'refund_id' => 'required|numeric',
            'check_status' => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $refund_id = $this->req->input('refund_id');
        $check_status = $this->req->input('check_status');
        $order = RefundOrder::with('order')->whereCompanyId($this->loginUser->company_id)->whereId($refund_id)->first();
        if (!$order) {
            return $this->errorJson('订单不存在');
        }
        if ($order->check_status > 0) {
            return $this->errorJson('订单无法再次审核!');
        }
        if ($check_status == 1) {
            if (!$api->refundApply($order)) {
                return $this->errorJson('发起退款失败,审核失败!');
            }
        }
        $order->check_status = $check_status;
        $order->save();
        return $this->successJson([], '审核成功！');
    }

    /**
     * 搜索条件
     * @return \Illuminate\Http\JsonResponse
     */
    public function conf()
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
        if ($order) {
            $flag = $api->make($order->pay_type)->findOrder($order);
            if ($flag) {
                return $this->successJson([], '订单已支付!');
            }
            return $this->errorJson('订单未支付!');
        }
        return $this->errorJson('订单不存在！');
    }
}

