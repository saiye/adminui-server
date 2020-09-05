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
            'refund_type' => 'required|in:1,2',
            'refund_reason_type' => 'required|numeric|in:1,2,3,4',
            'refund_reason' => 'required|max:100',
            'refund_fee' => 'required|numeric|min:0.01',
            'refundGoodsArr' => 'required|array',
        ], [
            'refund_reason_type.required' => '请选择退款原因！',
            'refund_reason.required' => '请输入与客人沟通情况！',
            'refund_type.required' => '退款类型错误！',
            'refund_fee.min' => '退款金额错误！',
            'refund_fee.numeric' => '退款金额错误！',
            'refund_fee.required' => '退款金额错误！',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first());
        }
        $refundGoodsArr = $this->req->input('refundGoodsArr',[]);
        $refund_type = $this->req->input('refund_type');
        $order_id = $this->req->input('order_id');
        $refund_fee = $this->req->input('refund_fee');
        $refund_reason = $this->req->input('refund_reason');

        $company_id = $this->loginUser->company_id;
        $store_id = $this->loginUser->store_id;
        if($refund_type==1){
            foreach ($refundGoodsArr as $sub) {
                $validator2 = Validator::make($sub, [
                    'order_goods_id' => 'required|numeric',
                    'type' => 'required|numeric',
                    'count' => 'required|numeric|min:1',
                ]);
                if ($validator2->fails()) {
                    return $this->errorJson($validator2->errors()->first());
                }
            }
        }
        if (!($refund_fee > 0)) {
            return $this->errorJson('退款金额有误！');
        }
        $order = Order::whereCompanyId($company_id)->whereOrderId($order_id)->first();
        if (!$order) {
            return $this->errorJson('订单不存在!');
        }
        if ($order->pay_status !== 1) {
            return $this->errorJson('订单未支付!');
        }
        //检查是否可以退款
        //申请中的金额+已经退款金额
        $totalApplyRefundFee = RefundOrder::whereOrderId($order_id)->whereIn('check_status', [0, 1])->sum('refund_fee');
        $canApplyFee = $order->actual_payment - $totalApplyRefundFee;
        if ($canApplyFee < 0) {
            $canApplyFee = 0;
        }
        if (!$canApplyFee > 0 or $canApplyFee < $refund_fee) {
            return $this->errorJson('申请失败，你目前可申请退款金额为:' . $canApplyFee);
        }
        //可以退款的话，生成申请记录
        $refundOrderArr = [
            'refund_no' => date('YmdHis') . mt_rand(111, 9999),
            'order_id' => $order_id,
            'refund_fee' => $refund_fee,
            'pay_type' => $order->pay_type,
            'refund_reason' => $refund_reason,
            'user_id' => $order->user_id,
            'company_id' => $company_id,
            'store_id' => $store_id,
        ];
        $refundOrder = RefundOrder::create($refundOrderArr);
        if($refundOrder){
            return $this->successJson([],'申请成功，等待审核！');
        }
        return $this->errorJson('申请失败!');
    }

    /**
     * 退款申请列表
     */
    public function refundApplyList()
    {
        $list = RefundOrder::with('order')->whereCompanyId($this->loginUser->company_id);
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

    public function refundConf()
    {
        $refundReasonType =Config::get('pay.refund_reason_type');
        $data = compact('refundReasonType');
        return $this->successJson($data);
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

