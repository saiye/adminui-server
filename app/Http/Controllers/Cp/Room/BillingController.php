<?php

namespace App\Http\Controllers\Cp\Room;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Billing;
use Illuminate\Support\Facades\Config;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class BillingController extends Controller
{

    public function billingList()
    {
        $data = new Billing();

        $data = $data->select(['billing.*','store.store_name','company.company_name'])->leftJoin('store', 'billing.store_id', '=', 'store.store_id')->leftJoin('company', 'billing.company_id', '=', 'company.company_id');

        if($this->req->company_id){
            $data=$data->where('billing.company_id',$this->req->company_id);
        }
        if($this->req->store_id){
            $data=$data->where('billing.store_id',$this->req->store_id);
        }
        if($this->req->time_nuit){
            $data=$data->whereTimeUnit($this->req->time_unit);
        }
        if ($this->req->search_name) {
            $data = $data->where('billing.billing_name', 'like', '%' . $this->req->search_name . '%')->orWhere('store.store_name', 'like', '%' . $this->req->search_name . '%');
        }
        if ($this->req->time_type) {
            $data = $data->where('billing.time_type', $this->req->time_type);
        }
        if ($this->req->price_type) {
            $data = $data->where('billing.price_type', $this->req->price_type);
        }
        $data = $data->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 添加房间
     */
    public function addBilling()
    {
        $validator = Validator::make($this->req->all(), [
            'billing_name' => 'required|max:15',
            'price' => 'required|min:0.1|numeric',
            'price_type' => 'required|max:150|numeric|min:1',
            'time_unit' => 'required|numeric|min:1|max:24',
            'time_type' => 'required|min:1',
            'storeArr' => 'required|array',
        ], [
            'billing_name.required' => '计费模式名称必须填写！',
            'billing_name.max' => '计费模式名称最长15字符！',
            'price.required' => '计费单价不能为空！',
            'price.min' => '计费单价最小0.1！',
            'price.numeric' => '计费单价必须是大于0.1的数字！',
            'time_unit.required' => '计费单位必须填写！',
            'time_unit.numeric' => '计费单位必须是数字！',
            'time_unit.min' => '计费单位最小是1！',
            'time_unit.max' => '计费单位最大是24！',
            'time_type.required' => '计费时间类型必须选择！',
            'storeArr.required' => '门店必须选择！',
            'storeArr.array' => '门店参数是一个数组！',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $storeArr=$this->req->storeArr;
        if(count($storeArr)!==2){
            return $this->errorJson('你未选择门店!!', 2);
        }
        $data = $this->req->except('use_time','storeArr');
        $data['company_id']=$storeArr[0];
        $data['store_id']=$storeArr[1];
        $room = Billing::create($data);
        if ($room) {
            return $this->successJson([], '操作成功');
        } else {
            return $this->errorJson('入库失败');
        }
    }

    /**
     * 返回计费相关设置
     */
    public function billingConfig()
    {
        $time_type = Config::get('deploy.time_type');
        $price_type = Config::get('deploy.price_type');
        $assign = compact('time_type', 'price_type');
        return $this->successJson($assign, '');
    }



}

