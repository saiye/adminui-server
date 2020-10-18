<?php
/**
 * Created by 2020/10/18 0018 16:18
 * User: buffer
 */

namespace App\Service\Store;


use App\Constants\PaginateSet;
use App\Models\Company;
use App\Models\Order;
use App\Models\ReceiptAccount;
use App\Models\WithdrawLog;
use Illuminate\Http\Request;

class OrderService
{

    /**
     * 某商户订单列表
     */
    public function companyOrderList($companyId)
    {
        return Order::whereCompanyId($companyId)->paginate(PaginateSet::LIMIT);
    }

    /**
     * 某店铺订单列表
     */
    public function storeOrderList($storeId)
    {
        return Order::whereStoreId($storeId)->paginate(PaginateSet::LIMIT);
    }

    /**
     * 商家今天收入
     */
    public function companyToDayIncome($companyId)
    {
        $date = date('Y-m-d');
        $data = Order::select(['store_id', DB::raw('sum("actual_payment")')])->with(['store' => function ($r) {
            $r->select(['store_id', 'store_name']);
        }])->whereCompanyId($companyId)->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])->groupBy('store_id')->get();

        //商家今天收入=订单金额-今天退款金额?

        return $data;
    }

    /**
     * 某商家提现记录
     */
    public function withdrawLogList($companyId)
    {
        return WithdrawLog::whereCompanyId($companyId)->paginate(PaginateSet::LIMIT);
    }

    /**
     * 商家收入提现申请
     */
    public function applyWithdrawal($companyId, $receiptAccountId, $withdrawFee)
    {
        $company = Company::whereCompanyId($companyId)->first();
        if (!$company) {
            return [false, "商家不存在！"];
        }
        $receiptAccount = ReceiptAccount::whereCompanyId($companyId)->whereId($receiptAccountId)->first();
        if (!$receiptAccount) {
            return [false, "提现账号不存在！"];
        }
        //检查可提现金额
        $res = $this->loadAmountAvailableForWithdrawal($companyId);
        if ($res['canWithdrawFee'] < $withdrawFee) {
            return [false, "可提现金额为:" . $res['canWithdrawFee']];
        }
        //现金抵扣
        $deductionFee = $withdrawFee * ($company->proportion / 100);
        //财务需打款金额
        $remitFee = $withdrawFee - $deductionFee;
        $data = WithdrawLog::create([
            'withdraw_no' => date('YmdHis') . mt_rand(1, 999),
            'withdraw_fee' => $withdrawFee,
            'deduction_fee' => $deductionFee,
            'remit_fee' => $remitFee,
            'pay_type' => $receiptAccount->pay_type,
            'company_id' => $companyId,
            'account' => $receiptAccount->account,
            'username' => $receiptAccount->username,
            'bank_name' => $receiptAccount->bank_name,
        ]);
        return [true, $data];
    }

    /**
     * 某商家可以提现金额
     * @param $companyId
     * @return array
     */
    public function loadAmountAvailableForWithdrawal($companyId)
    {
        //订单总额
        $orderTotalMoney = Order::whereCompanyId($companyId)->wherePayStatus(1)->sum('withdraw_fee');
        //提现申请总额
        $applyRes = WithdrawLog::select([DB::raw('sum(deduction_fee) as total_deduction_fee'), DB::raw('sum(withdraw_fee) as total_withdraw_fee')])->whereCompanyId($companyId)->whereIn('check_status', [0, 1])->first();
        //总提现金额
        $totalWithdrawFee = 0;
        //总抵扣金额=总提现金额*商家分成比例
        $totalDeductionFee = 0;
        if ($applyRes) {
            $totalWithdrawFee = $applyRes['total_withdraw_fee'];
            $totalDeductionFee = $applyRes['total_deduction_fee'];
        }
        //目前可提现金额
        $canWithdrawFee = $orderTotalMoney - $totalWithdrawFee;
        if ($canWithdrawFee < 0) {
            $canWithdrawFee = 0;
        }
        return compact('orderTotalMoney', 'canWithdrawFee', 'totalDeductionFee', 'totalWithdrawFee');
    }

    public function companyReceiptAccountList($companyId)
    {
        return ReceiptAccount::whereCompanyId($companyId)->get();
    }


}
