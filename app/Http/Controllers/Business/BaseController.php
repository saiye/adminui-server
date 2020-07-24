<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class BaseController extends Controller
{
    public $loginUser=null;

    public function  __construct(Request $req)
    {
        parent::__construct($req);
        $this->loginUser=AUth::guard('staff')->user();
    }

    public function project(){
        return 'business';
    }
}
