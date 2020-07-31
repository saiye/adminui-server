<?php

namespace App\Http\Controllers\Business\Tool;

use App\Constants\CacheKey;
use  App\Http\Controllers\Business\BaseController as Controller;
use App\Models\GoodsImage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Validator;
use Illuminate\Support\Facades\Storage;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class GoodsController extends Controller
{
    /**
     * 图片上传
     */
    public function upload()
    {
        $validator = Validator::make($this->req->all(), [
            'file' => 'required|image',
            'goods_id' => 'numeric',
        ], [
            'file.required' => 'file字段不存在',
            'file.image' => '不是图片类型文件！',
            'goods_id.numeric' => 'goods_id是一个数字',
        ]);
        if ($validator->fails()) {
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $env=Config::get('app.env');
        $path = $this->req->file('file')->store('app/'.$env.'/goods');
        if (!$path) {
            $this->errorJson('图片上传失败！');
        }
        $url = Storage::url($path);
        /**
         * 入库处理
         */
        $image = GoodsImage::create([
            'image' => $path,
            'goods_id' => $this->req->input('goods_id',0),
            'store_id' =>$this->loginUser->store_id,
            'company_id' =>$this->loginUser->company_id,
        ]);
        if (!$image) {
            //物理文件删除操作
            Storage::delete($path);
            $this->errorJson('上传成功，入库失败!');
        }
        //标记图片是你上传的
        $key=CacheKey::STAFF_UPLOAD_KEY.$this->loginUser->staff_id;
        $imageJson=Cache::get($key,'');
        if($imageJson){
            $tmp=json_decode($imageJson,true);
            $imageData=array_merge($tmp,[$image->goods_image_id]);
        }else{
            $imageData=[$image->goods_image_id];
        }
        Cache::put($key,json_encode($imageData),3600);
        return $this->successJson([
            'id' => $image->goods_image_id,
            'path' => $path,
            'url' => $url,
            'imageData' => $imageData,
        ],'上传成功！');
    }

    /**
     * 商品图片单文件图片删除
     */
    public function delete()
    {
        $validator = Validator::make($this->req->all(), [
            'id' => 'required|numeric',
        ], [
            'id.required' => 'id是必须的',
            'id.numeric' => 'id是一个数字',
        ]);
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first(), 2);
        }
        $image = GoodsImage::whereGoodsImageId($this->req->id)->first();
        if ($image) {
            $image->update([
                'is_del' => 1,
            ]);
            return $this->successJson([], '成功删除!');
        }
        return $this->errorJson('不存在图片');
    }


}

