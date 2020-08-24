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
        $sumPriceCount=0;
        //计算各个组合价格
        $groupPrice=[];
        foreach ($config as $item) {
            $validator2 = Validator::make($item, [
                'tag_name' => 'required|max:50',
                'tags' => 'required|array',
            ], [
                'tag_name.required' => '标签名不能为空',
                'tag_name.max' => '标签不能超过50字符',
                'tags.required' => '规格不能为空',
                'tags.array' => '规格只能是个数组',
            ]);
            if ($validator2->fails()) {
                return [false, $validator2->errors()->first()];
            }
            $tmpPrice=[];
            foreach ($item['tags'] as $sub) {
                $validator3 = Validator::make($sub, [
                    'sku_name' => 'required|max:50',
                    'goods_price' => 'required|numeric|min:0|max:900000',
                    'active' => 'required|numeric|in:0,1',
                    'stock' => 'required|numeric|min:0',
                ], [
                    'sku_name.required' => '规格名称不能为空',
                    'sku_name.max' => '规格名称最长50字符',
                    'goods_price.required' => '规格id最小只能是0',
                    'goods_price.numeric' => '价格只能是数字',
                    'goods_price.min' => '价格最小只能是0',
                    'goods_price.max' => '价格不能大于90万',
                    'stock.required' => '库存不能为空',
                    'stock.numeric' => '库存是一个数字',
                    'stock.min' => '最小库存是0',
                ]);
                if ($validator3->fails()) {
                    return [false, $validator3->errors()->first(),[]];
                }
                if($sub['active']==1){
                    $sumPriceCount+=1;
                    $price+=$sub['goods_price'];
                    array_push($defaultTagArr,$sub['sku_name']);
                }
                array_push($tmpPrice,$sub['goods_price']+0);
            }
            sort($tmpPrice);
            array_push($groupPrice,$tmpPrice);
        }
        if($sumPriceCount!=count($config)){
            return [false,'默认规格选择错误!',[]];
        }
        if(!self::checkPrice($groupPrice)){
            return [false,'默认规格存在组合价格为零，入库失败!',[]];
        }
        return [true, 'success',[
            'price'=>$price,
            'defaultTagArr'=>$defaultTagArr,
        ]];
    }
    public static function checkPrice($groupPrice){
        $minPrice=0;
        foreach ($groupPrice as $group){
            foreach ($group as $price){
                $minPrice+=$price;
                break;
            }
        }
        return $minPrice>0;
    }
}
