<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/6/29
 * Time: 17:43
 */

namespace App\Http\Controllers\Wx;
use \Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Base extends Controller
{
    public $validationFactory;
    protected $request;
    protected $app;

    public $st = ' 00:00:00';
    public $et = ' 23:59:59';


    public function __construct(Application $app,Request $request)
    {
        $this->app = $app;
        $this->request = $request;
        $this->validationFactory =$app->make('validator');
    }

    public function json($data, $status = 200)
    {
        return response()->json($data, $status);
    }

    public function user(){
        Auth::guard('users')->user();
    }
}
