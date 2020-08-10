<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/10
 * Time: 10:39
 */

namespace App\TraitInterface;


trait ApiTrait
{
    public function json($data, $status = 200)
    {
        return response()->json($data, $status);
    }
}
