<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsSku extends Model
{
    protected $table = 'goods_sku';
    public  $primaryKey='sku_id';
    protected $guarded = [
        'sku_id'
    ];

    /**
     * 添加商品规格数据
     */
    public static function addSku($goods, $config, $user)
    {
        //1.add goods_tag
        $date=date('Y-m-d H:i:s');
        foreach ($config as $val) {
            $item = [
                'tag_name' => $val['tag_name'],
                'store_id' => $user->store_id,
                'company_id' => $user->company_id,
                'category_id' => $goods->category_id,
            ];
            $saveTag = GoodsTag::create($item);
            if ($saveTag) {
                //save ku
                $skuArr = [];
                foreach ($val['tags'] as $sku) {
                    unset($sku['sku_id']);
                    $sku['tag_id'] = $saveTag->tag_id;
                    $sku['goods_id'] = $goods->goods_id;
                    $sku['created_at'] = $date;
                    $sku['updated_at'] = $date;
                    array_push($skuArr, $sku);
                }
                $saveSku = GoodsSku::insert($skuArr);
                if (!$saveSku) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

}
