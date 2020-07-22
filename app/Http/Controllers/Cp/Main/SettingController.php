<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Http\Controllers\Cp\Main;

use App\Constants\PaginateSet;
use App\Http\Controllers\Cp\BaseController;
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
            'key' => 'required|max:30|unique:web_config',
            'value' => 'required|array',
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
        $hasItem = WebConfig::whereKey($this->req->key)->first();
        if ($hasItem) {
            if ($hasItem->id !== $this->req->id) {
                return $this->errorJson('key重复！');
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
        $webConfig=WebConfig::cache_file;
        $res=WebConfig::all();
        $data=[];
        foreach ($res as $val){
            $data[$val->key]=$val->format;
        }
        $content="<?php\n return\t".var_export($data,true).';';
        $isOK= Storage::disk('local')->put($webConfig,$content);
        if($isOK){
            return $this->successJson([], '刷新成功！');
        }
        return $this->errorJson('刷新失败');
    }


}
