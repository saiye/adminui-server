<?php
namespace App\Http\Controllers\Www;
use \Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\TraitInterface\ApiTrait;
class BaseController extends Controller
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

    public function json($data, $status = 200)
    {
        return response()->json($data, $status);
    }

    public function user(){
        return  Auth::guard('wx')->user();
    }
}
