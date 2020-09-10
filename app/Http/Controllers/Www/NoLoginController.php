<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/7/3
 * Time: 17:08
 */

namespace App\Http\Controllers\Www;


use App\Constants\ErrorCode;
use App\TraitInterface\BaseTrait;

class NoLoginController extends BaseController
{
    use  BaseTrait;
    public function apiNoLogin()
    {
        return $this->json(['errorMessage' => trans('user.not_login'),
            'code' => ErrorCode::ACCOUNT_NOT_LOGIN]);
    }
    public function adminNoLogin()
    {
        return $this->errorJson(trans('user.not_login'));
    }

}
