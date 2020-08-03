<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsSku extends Model
{
    protected $table = 'goods_sku';
    public $primaryKey = 'sku_id';
    protected $guarded = [
        'sku_id'
    ];

    /**
     * 添加商品规格数据
     */
    public static function addSku($goods, $config, $user)
    {
        //1.add goods_tag
        $date = date('Y-m-d H:i:s');
        //库存统计
        $totalStock = 0;
        foreach ($config as $val) {
            $item = [
                'tag_name' => $val['tag_name'],
                'store_id' => $user->store_id,
                'company_id' => $user->company_id,
                'category_id' => $goods->category_id,
            ];
            $saveTag = GoodsTag::firstOrCreate($item);
            if ($saveTag) {
                //save ku
                $skuArr = [];
                foreach ($val['tags'] as $sku) {
                    $totalStock += $sku['stock'];
                    array_push($skuArr, [
                        'tag_id'=>$saveTag->tag_id,
                        'goods_id'=>$goods->goods_id,
                        'stock'=>$sku['stock'],
                        'goods_price'=>$sku['goods_price'],
                        'sku_name'=>$sku['sku_name'],
                        'active'=>$sku['active'],
                        'created_at'=>$date,
                        'updated_at'=>$date,
                    ]);
                }
                $saveSku = GoodsSku::insert($skuArr);
                if (!$saveSku) {
                    return [false, $totalStock];
                }
            } else {
                return [false, $totalStock];
            }
        }
        return [true, $totalStock];
    }

    /**
     * 修改sku
     */
    public static function editSku()
    {

    }

    public function tag()
    {
        return $this->hasOne(GoodsTag::class, 'tag_id', 'tag_id');
    }

}
