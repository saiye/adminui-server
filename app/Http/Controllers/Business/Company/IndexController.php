<?php

namespace App\Http\Controllers\Business\Company;

use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\Area;
use Validator;
use Illuminate\Support\Facades\Config;


/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class IndexController extends Controller
{
    public function getState()
    {
        $data = Config::get('deploy.state');
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function areaList()
    {
        $validator = Validator::make($this->req->all(), [
            'parent_id' => 'required|numeric',
        ], [
            'parent_id.required' => 'parent_id必须',
            'parent_id.numeric' => 'parent_id一个数字',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $res = Area::whereParentId($this->req->parent_id)->get();
        $data = [];
        foreach ($res as $val) {
            array_push($data, [
                'value' => $val->area_id,
                'label' => $val->area_name,
                'parent_id' => $val->parent_id,
                'level' => $val->level,
                'leaf' => $val->level == 3
            ]);
        }
        $assign = compact('data');
        return $this->successJson($assign);
    }
}

