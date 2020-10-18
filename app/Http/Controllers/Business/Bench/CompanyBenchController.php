<?php

namespace App\Http\Controllers\Business\Bench;

use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Company;
use App\Models\Order;
use App\Models\ReceiptAccount;
use App\Models\Store;
use App\Models\WithdrawLog;
use App\Service\Store\OrderService;
use App\Service\Store\RoomService;

/**
 *
 * @author buffer
 */
class CompanyBenchController extends Controller
{
    /**
     * 商家工作台总览
     */
    public function indexCompanyBenchOverview()
    {

    }

    /**
     * 商家查看某，店铺房间使用情况
     */
    public function storeRoomList(RoomService $roomService)
    {
        $companyId = $this->loginUser->company_id;
        $storeId = $this->req->input('store_id');
        $store = Store::whereCompanyId($companyId)->whereStoreId($storeId)->first();
        if (!$store) {
            return $this->errorJson("店铺不存在！");
        }
        $roomList = $roomService->storeRoomList($storeId);
        $useTotal = $roomService->storeRoomUseTotal($storeId);
        $data = compact('roomList', 'useTotal');
        return $this->successJson($data);
    }

    /**
     * 查看某商家，可提现金额
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadAmountAvailableForWithdrawal(OrderService $orderService)
    {
        $companyId = $this->loginUser->company_id;

        $data = $orderService->loadAmountAvailableForWithdrawal($companyId);

        $data['receiptAccountList']=$orderService->companyReceiptAccountList($companyId);
        return $this->successJson($data);
    }

    /**
     * 商家申请提现
     */
    public function doApplyWithdraw(OrderService $orderService)
    {
        $validator = Validator::make($this->req->all(), [
            'withdraw_fee' => 'required|numeric|min:0.01',
            'receipt_account_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        //提现金额
        $withdrawFee=$this->req->input('withdraw_fee');
        //提现账号id
        $receiptAccountId=$this->req->input('receipt_account_id');

        $companyId = $this->loginUser->company_id;

        list($status,$data) = $orderService->applyWithdrawal($companyId,$receiptAccountId,$withdrawFee);
        if($status){
            return $this->successJson($data);
        }
        return $this->errorJson($data);
    }


}

