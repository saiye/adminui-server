<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/6/12
 * Time: 11:41
 */

namespace App\TraitInterface;


trait ModelDataFormat
{
    public function getUpdatedAtAttribute($value)
    {
        return $value ? date("Y-m-d H:i:s", strtotime($value)) : '';
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? date("Y-m-d H:i:s", strtotime($value)) : '';
    }
}
