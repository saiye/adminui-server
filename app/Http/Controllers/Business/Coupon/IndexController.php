<?php

namespace App\Http\Controllers\Business\Coupon;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\GoodsCoupon;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class IndexController extends Controller
{

    public function couponList()
    {
        $data = new GoodsCoupon();
        if ($this->req->coupon_name) {
            $data = $data->where('coupon_name', 'like', '%' . $this->req->coupon_name . '%');
        }
        if ($this->req->is_del) {
            $data = $data->where('is_del', $this->req->is_del);
        }
        $data = $data->orderBy('coupon.coupon_id', 'desc')->paginate($this->req->input('limit', $limit = $this->req->input('limit', PaginateSet::LIMIT)))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }


    public function addGoods()
    {

    }

    public function editGoods()
    {

    }
}

