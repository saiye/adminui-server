<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/6/30
 * Time: 14:41
 */

namespace App\Service\LoginApi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

abstract class BaseLoginApi
{
    public $request = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 获取api相关配置数组
     */
    public function config()
    {
        $type = $this->type();

        return Config::get('auth2.' . $type, []);
    }

    /**
     * 保存二维码到目录
     */
    public function saveErCode($buffer)
    {
        
    }

    abstract protected function checkLogin();

    abstract protected function type();
}
