<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Http\Controllers\Cp\Main;

use App\Models\ActionLog;
use App\Models\ApiActionLog;
use Illuminate\Http\Request;
use  App\Http\Controllers\Cp\BaseController as Controller;
use Config;
use App\Models\LoginLog;
/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class LogController extends Controller
{

    public function getError(){
        $file_path = $this->_get_path();
        $data = array();
        foreach (glob($file_path.DIRECTORY_SEPARATOR.'*.log') as $file){
            array_push($data,[
               'file'=> basename($file)
            ]);
        }
        $total=count($data);
        $assign = compact('data','total');
        return $this->successJson($assign);
    }

    public function _get_path(){
       return base_path('storage'.DIRECTORY_SEPARATOR.'logs');
    }

    public function getLog(Request $req){
        $filename=$req->input('filename');
        $file_path = $this->_get_path();
        $file=$file_path.DIRECTORY_SEPARATOR.$filename;
        if(is_file($file))
            $content =file_get_contents($file);
        else
            $content='';
        $assign = compact('content');
        return $this->successJson($assign);
    }

    public function getActionLog(){
        $data= new ActionLog();
        if($this->req->uri){
            $data=$data->whereUri($this->req->uri);
        }
        $data=$data->orderBy('id','desc')->paginate(30)->appends($this->req->except('page'));
        $assign=compact('data');
        return $this->successJson($assign);
    }


}

