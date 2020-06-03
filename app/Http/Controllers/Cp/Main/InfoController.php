<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Http\Controllers\Cp\Main;

use Illuminate\Http\Request;
use  App\Http\Controllers\Cp\BaseController as Controller;
use Cache;
/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class InfoController extends Controller
{

    public function getPhpinfo(){
        return $this->view('main.info.phpinfo');
    }
    public function getClearCache(){
        Cache::flush();
        if(function_exists('opcache_reset')){
            opcache_reset();
        }
        return $this->successJson([],'缓存清除成功！');
    }

    /**
     * 系统探针
     */
    public function getProbe(){
        return $this->view('main.info.probe');
    }


}
