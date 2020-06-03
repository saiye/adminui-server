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
            $data[$file]=basename($file);
        }
        if(empty($data)){
            return $this->errorJson('无日志文件！');
        }
        $assign = compact('data');
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
        return $this->view('main.log.log',$assign);
    }

    public function getActionLog(){
        $data= new ActionLog();
        if($this->req->uri){
            $data=$data->whereUri($this->req->uri);
        }
        $data=$data->orderBy('id','desc')->paginate(30)->appends($this->req->except('page'));
        $assign=compact('data');
        return $this->view('main.log.action_log',$assign);
    }

    public function getApiActionLog(){
        $data= new ApiActionLog();
        if($this->req->ip){
            $data=$data->whereIp($this->req->ip);
        }
        if($this->req->uri){
            $data=$data->whereUri($this->req->uri);
        }
        if($this->req->date_start){
            $data=$data->where('date','>=',$this->req->date_start);
        }
        if($this->req->date_end){
            $data=$data->where('date','<=',$this->req->date_end);
        }
        $data=$data->orderBy('id','desc')->paginate(30)->appends($this->req->except('page'));
        $assign=compact('data');
        return $this->view('main.log.api_action_log',$assign);
    }
    public function getLoginLog(){
        $data= new LoginLog();
        if($this->req->user){
            $data=$data->where('user','like','%'.$this->req->user.'%');
        }
        if($this->req->user_id){
            $data=$data->whereUserId($this->req->user_id);
        }
        if($this->req->action_type){
            $data=$data->whereActionType($this->req->action_type);
        }
        $data=$data->orderBy('id','desc')->paginate(30)->appends($this->req->except('page'));
        $assign=compact('data');
        return $this->view('main.log.login_log',$assign);
    }

}

