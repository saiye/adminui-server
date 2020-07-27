<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsQuickCat extends Model
{
    protected $table = 'goods_quick_tag';
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
        foreach ($config as $item) {
            $validator2 = Validator::make($item, [
                'tag_name' => 'required|max:30',
                'config' => 'required|array',
            ], [
                'tag_name.required' => '标签名不能为空',
                'tag_name.max' => '标签不能超过30字符',
                'config.required' => '配置不能为空',
                'config.array' => '配置只能是个数组',
            ]);
            if ($validator2->fails()) {
                return [false, $validator2->errors()->first()];
            }
        }

        return [true, 'success'];
    }
}
