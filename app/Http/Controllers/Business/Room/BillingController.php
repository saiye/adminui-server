<?php

namespace App\Http\Controllers\Business\Room;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
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

        $company_id = $this->loginUser->company_id;


        $data = $data->select(['billing.*', 'company.company_name'])->leftJoin('company', 'billing.company_id', '=', 'company.company_id');

        $data = $data->where('billing.company_id', $company_id);

        if ($this->req->time_nuit) {
            $data = $data->whereTimeUnit($this->req->time_unit);
        }
        if ($this->req->search_name) {
            $data = $data->where('billing.billing_name', 'like', '%' . $this->req->search_name . '%');
        }
        if ($this->req->time_type) {
            $data = $data->where('billing.time_type', $this->req->time_type);
        }
        if ($this->req->price_type) {
            $data = $data->where('billing.price_type', $this->req->price_type);
        }
        $data = $data->orderBy('billing.billing_id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
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
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data = $this->req->except('use_time');

        $company_id = $this->loginUser->company_id;
        $store_id = $this->loginUser->store_id;

        $data['company_id'] = $company_id;
        $data['store_id'] = $store_id;
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

