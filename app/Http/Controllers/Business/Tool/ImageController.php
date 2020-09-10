<?php

namespace App\Http\Controllers\Business\Tool;

use App\Constants\CacheKey;
use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Validator;
use Illuminate\Support\Facades\Storage;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class ImageController extends Controller
{
    /**
     * 图片上传
     */
    public function upload()
    {
        $validator = Validator::make($this->req->all(), [
            'file' => 'required|image',
            'type' => 'required|numeric|in:1,2',
            'foreign_id' => 'numeric',
        ], [
            'file.required' => 'file字段不存在',
            'file.image' => '不是图片类型文件！',
            'type.required' => 'type required',
            'type.numeric' => 'type 是一个数字',
            'type.in' => 'type 1商户营业执照,2店面照片',
            'foreign_id.numeric' => '外键是一个数字',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $env=Config::get('app.env');
        $path = $this->req->file('file')->store('app/'.$env.'/company');
        if (!$path) {
            $this->errorJson('图片上传失败！');
        }
        $url = Storage::url($path);
        /**
         * 入库处理
         */
        $image = Image::create([
            'path' => $path,
            'type' => $this->req->type,
            'foreign_id' => $this->req->input('foreign_id',0),
        ]);
        if (!$image) {
            //物理文件删除操作
            Storage::delete($path);
            $this->errorJson('上传成功，入库失败!');
        }
        $user=Auth::guard('staff')->user();
        //标记图片是你上传的
        $key=CacheKey::CP_UPLOAD_KEY.$user->id;
        $imageJson=Cache::get($key,'');
        if($imageJson){
            $tmp=json_decode($imageJson,true);
            $imageData=array_merge($tmp,[$image->id]);
        }else{
            $imageData=[$image->id];
        }
        Cache::put($key,json_encode($imageData),3600);
        return $this->successJson([
            'id' => $image->id,
            'path' => $path,
            'url' => $url,
            'image_id' => $image->id,
            'imageData' => $imageData,
        ],'上传成功！');
    }

    /**
     * 单文件图片删除
     */
    public function delete()
    {
        $validator = Validator::make($this->req->all(), [
            'image_id' => 'required|numeric',
        ], [
            'image_id.required' => '外键不能为空',
            'image_id.numeric' => '外键是一个数字',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $image = Image::whereId($this->req->image_id)->first();
        if ($image) {
            $image->update([
                'is_del' => 1,
            ]);
            return $this->successJson([], '成功删除!');
        }
        return $this->errorJson('不存在图片');
    }


}

