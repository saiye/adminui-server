<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\TraitInterface\BaseTrait;
use Illuminate\Support\Facades\Auth;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests,BaseTrait;

    public  $req;
    public $st=' 00:00:00';
    public $et=' 23:59:59';
    public $loginUser=null;
    public function __construct(Request $req)
    {
        $this->req=$req;
        $this->loginUser=AUth::guard('staff')->user();
    }

    public function view($file,$data=[],$time=5){
        $project=$this->project();
        return view($project.'.'.$file, $data);
    }
    protected  function project(){
        return 'cp';
    }
}
