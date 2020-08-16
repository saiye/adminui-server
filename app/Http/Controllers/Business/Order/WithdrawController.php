<?php

namespace App\Http\Controllers\Business\Order;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Order;
use App\Models\ReceiptAccount;
use App\Models\WithdrawLog;
use Validator;
use Illuminate\Support\Facades\Config;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class WithdrawController extends Controller
{

    public function orderList()
    {
        $data = new WithdrawLog();
        $data = $data->with(['company' => function ($r) {
            $r->select('staff_id', 'company_id', 'company_name')->with(['manage' => function ($q) {
                $q->select('staff_id', 'phone', 'account', 'real_name');
            }]);
        }])->where('company_id', $this->loginUser->company_id);
        $searchOrderSn = $this->req->input('searchOrderSn');
        if ($searchOrderSn) {
            $data->where('withdraw_no', $searchOrderSn);
        }
        if ($this->req->pay_type) {
            $data = $data->wherePayType($this->req->pay_type);
        }
        if ($this->req->check_status) {
            $data = $data->whereCheckStatus($this->req->check_status);
        }
        if ($this->req->listDate) {
            $start_date = $this->req->listDate[0];
            $end_date = $this->req->listDate[1];
            $data = $data->whereBetween('created_at', [$start_date, $end_date]);
        }
        $data = $data->orderBy('id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function setStatus()
    {
        $validator = Validator::make($this->req->all(), [
            'withdraw_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $data = WithdrawLog::whereCompanyId($this->loginUser->company_id)->whereId($this->req->withdraw_id)->first();
        if (!$data) {
            return $this->errorJson('订单不存在');
        }
        if ($data->check_status !== 1) {
            $data->check_status = 3;
            $data->save();
            return $this->successJson([], '操作成功！');
        }
        return $this->errorJson('操作失败');
    }

    /**
     * 添加提现账户
     */
    public function addReceiptAccount()
    {

        $validator = Validator::make($this->req->all(), [
            'account' => 'required|max:50',
            'pay_type' => 'required',
            'active' => 'required|in:0,1',
            'username' => 'required|max:20',
            'bank_name' => 'required|max:100',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $company_id = $this->loginUser->company_id;
        $data = $this->req->only(['account', 'pay_type', 'active', 'username', 'bank_name']);
        $data['company_id'] = $company_id;
        $createAccount = ReceiptAccount::create($data);
        if ($createAccount) {
            return $this->successJson([], '操作成功！');
        }
        return $this->errorJson('操作失败');
    }

    /**
     * 添加提现账户
     */
    public function editReceiptAccount()
    {
        $validator = Validator::make($this->req->all(), [
            'id' => 'required|numeric',
            'account' => 'required|max:50',
            'pay_type' => 'required',
            'active' => 'required|in:0,1',
            'username' => 'required|max:20',
            'bank_name' => 'required|max:100',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $company_id = $this->loginUser->company_id;
        $data = $this->req->only(['account', 'pay_type', 'active', 'username', 'bank_name']);
        $updateAccount = ReceiptAccount::whereCompanyId($company_id)->whereId($this->req->id)->update($data);
        if ($updateAccount) {
            return $this->successJson([], '操作成功！');
        }
        return $this->errorJson('操作失败');
    }

    public function receiptAccountList()
    {
        $data = new ReceiptAccount();
        $data = $data->where('company_id', $this->loginUser->company_id);
        $account = $this->req->input('account');
        if ($account) {
            $data->where('account', $account);
        }
        if ($this->req->pay_type) {
            $data = $data->wherePayType($this->req->pay_type);
        }
        $data = $data->orderBy('id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function conf()
    {
        $payTypeListConf=Config::get('pay.pay_type');
        $checkStatusListConf=Config::get('pay.check_status');
        $payTypeList=$checkStatusList=[];
        $payTypeList=$this->formatType($payTypeListConf);
        $checkStatusList=$this->formatType($checkStatusListConf);
        $assign = compact('payTypeList','checkStatusList');
        return $this->successJson($assign);
    }

    private function formatType($res){
        $data=[];
        foreach ($res as $k=>$v){
            array_push($data,[
                'id'=>$k,
                'name'=>$v,
            ]);
        }
        return $data;
    }


}

