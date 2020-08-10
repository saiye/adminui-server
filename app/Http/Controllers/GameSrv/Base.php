<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/6/29
 * Time: 17:43
 */

namespace App\Http\Controllers\GameSrv;
use App\TraitInterface\ApiTrait;
use \Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;


class Base extends Controller
{
    use ApiTrait;
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

    public function  getBody(){

    }

}
