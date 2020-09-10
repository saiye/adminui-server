<?php

namespace App\Http\Controllers\Cp\Main;

use App\Constants\PaginateSet;
use App\Http\Controllers\Cp\BaseController;
use App\Models\CountryZone;
use App\Models\NoteSms;
use App\Models\WebConfig;
use Illuminate\Support\Facades\Storage;
use Redirect;
use Route;
use Validator;

class SettingController extends BaseController
{

    public function getList()
    {
        $data = new WebConfig();
        if ($this->req->key) {
            $data = $data->where('key', $this->req->key);
        }
        if ($this->req->value) {
            $data = $data->where('value', 'like', '%' . $this->req->value . '%');
        }
        $data = $data->paginate(PaginateSet::LIMIT)->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function postAdd()
    {
        $validator = Validator::make($this->req->all(), [
            'key' => 'required|max:30|min:1|unique:web_config',
            'value' => 'required|array',
        ], [
            'key.unique' => 'key重复',
            'key.max' => 'key长度不能超过30',
            'key.min' => 'key长度不能小于1',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $valueData = $this->req->input('value', []);
        if (!$this->checkValue($valueData)) {
            return $this->errorJson('配置不能为空!', 2);
        }
        $data = $this->req->only(['key', 'value']);
        WebConfig::create($data);
        return $this->successJson([], '添加成功！');
    }

    public function checkValue($valueData)
    {
        foreach ($valueData as $item) {
            $validator2 = Validator::make($item, [
                'k' => 'required|min:1',
                'v' => 'required|min:1',
            ]);
            if ($validator2->fails()) {
                return false;
            }
        }
        return true;
    }

    public function postEdit()
    {
        $validator = Validator::make($this->req->all(), [
            'id' => 'required|exists:web_config',
            'key' => 'required|max:30',
            'value' => 'required|array',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $valueData = $this->req->input('value', []);
        if (!$this->checkValue($valueData)) {
            return $this->errorJson('配置不能为空!', 2);
        }
        $hasItem = WebConfig::where('key', $this->req->key)->first();
        if ($hasItem) {
            if (!($hasItem->id == $this->req->id)) {
                return $this->errorJson('key值重复！');
            }
        }
        $data = $this->req->only(['key', 'value']);
        WebConfig::whereId($this->req->id)->update($data);
        return $this->successJson([], '修改成功！');
    }

    /**
     * 刷新配置到文件
     */
    public function putConfigToFile()
    {
        $webConfig = WebConfig::cache_file;
        $res = WebConfig::all();
        $data = [];
        foreach ($res as $val) {
            $data[$val->key] = $val->format;
        }
        $content = "<?php\n return\t" . var_export($data, true) . ';';
        $isOK = Storage::disk('local')->put($webConfig, $content);
        if ($isOK) {
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            return $this->successJson([], '刷新成功！');
        }
        return $this->errorJson('刷新失败');
    }

    public function sendSmsList()
    {
        $list = new NoteSms();
        if ($this->req->area_code) {
            $list = $list->where('area_code', $this->req->area_code);
        }
        if ($this->req->phone) {
            $list = $list->where('phone', $this->req->phone);
        }
        if ($this->req->type) {
            $list = $list->where('type', $this->req->type);
        }
        $data = $list->orderBy('id', 'desc')->paginate(PaginateSet::LIMIT)->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    /**
     * 地区列表
     */
    public function areaList()
    {
        $list = new CountryZone();
        if ($this->req->area_code) {
            $list = $list->where('area_code', $this->req->area_code);
        }
        if ($this->req->search_name) {
            $list = $list->where('name_zh_cn', 'like', '%' . $this->req->search_name.'%');
        }
        $data = $list->orderBy('id', 'desc')->paginate(PaginateSet::LIMIT)->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }

    public function addArea()
    {
        $validator = Validator::make($this->req->all(), [
            'name_zh_cn' => 'required',
            'name_en' => 'required',
            'area_code' => 'required',
            'letter_en' => 'required',
            'letter_zh_cn' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $data = $this->req->input();
        $save = CountryZone::create($data);
        if ($save) {
            return $this->successJson([], '创建成功！');
        }
        return $this->errorJson('创建失败!');
    }

    public function editArea()
    {
        $validator = Validator::make($this->req->all(), [
            'id' => 'required|numeric',
            'name_zh_cn' => 'required',
            'name_en' => 'required',
            'area_code' => 'required',
            'letter_en' => 'required',
            'letter_zh_cn' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $id = $this->req->input('id');
        $data = $this->req->except('id');
        $isUp = CountryZone::whereId($id)->update($data);
        if ($isUp) {
            return $this->successJson([], '修改成功！');
        }
        return $this->errorJson('修改失败!');
    }

}
