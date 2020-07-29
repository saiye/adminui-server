<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Validator;

class GoodsQuickCat extends Model
{
    protected $table = 'goods_quick_tag';
    public $timestamps = false;
    protected $guarded = [
        'id'
    ];

    //移动到的分类id
    public function getConfigAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setConfigAttribute($value)
    {
        $this->attributes['config'] = json_encode($value);
    }


    public static function checkConfig($config)
    {
        $price=0;
        $defaultTagArr=[];
        foreach ($config as $item) {
            $validator2 = Validator::make($item, [
                'tag_name' => 'required|max:50',
                'tag_id' => 'required|numeric|min:0',
                'tags' => 'required|array',
            ], [
                'tag_name.required' => '标签名不能为空',
                'tag_name.max' => '标签不能超过50字符',
                'tag_id.required' => '标签id必须的',
                'tag_id.numeric' => '标签id是一个数字',
                'tags.required' => '规格不能为空',
                'tags.array' => '规格只能是个数组',
            ]);
            if ($validator2->fails()) {
                return [false, $validator2->errors()->first()];
            }
            foreach ($item['tags'] as $sub) {
                $validator3 = Validator::make($sub, [
                    'sku_name' => 'required|max:50',
                    'sku_id' => 'required|numeric|min:0',
                    'goods_price' => 'required|numeric|min:0.01',
                ], [
                    'sku_name.required' => '规格名称不能为空',
                    'sku_name.max' => '规格名称最长50字符',
                    'sku_id.min' => '规格id最小只能是0',
                    'sku_id.numeric' => '规格id只能个数字',
                    'goods_price.required' => '规格id最小只能是0',
                    'goods_price.numeric' => '价格只能是数字',
                    'goods_price.min' => '价格最小只能是0.01',
                ]);
                if ($validator3->fails()) {
                    return [false, $validator3->errors()->first(),[]];
                }
                $price+=$sub['goods_price'];
                array_push($defaultTagArr,$sub['sku_name']);
            }
        }
        return [true, 'success',[
            'price'=>$price,
            'defaultTagArr'=>$defaultTagArr,
        ]];
    }
}
