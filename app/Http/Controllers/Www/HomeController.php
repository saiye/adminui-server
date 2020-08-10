<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/7/3
 * Time: 17:08
 */

namespace App\Http\Controllers\Www;


use App\Constants\ErrorCode;
use App\TraitInterface\ApiTrait;
use App\TraitInterface\BaseTrait;

class HomeController extends BaseController
{
    use ApiTrait, BaseTrait;

    public function home()
    {
        return 'hello boy';
    }
}
