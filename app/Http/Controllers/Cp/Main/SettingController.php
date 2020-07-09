<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Http\Controllers\Cp\Main;
use App\Constants\PaginateSet;
use App\Http\Controllers\Cp\BaseController;
use App\Models\Setting;
use Redirect;
use Illuminate\Support\Facades\Auth;
use Route;


class SettingController extends BaseController
{

    public function getList(){
        $data= new Setting();
        if($this->req->user){
            $data=$data->where('user','like','%'.$this->req->user.'%');
        }
        if($this->req->type){
            $data=$data->whereType($this->req->type);
        }
        if($this->req->tid){
            $data=$data->whereTid($this->req->tid);
        }
        $data=$data->paginate(PaginateSet::LIMIT)->appends($this->req->except('page'));
        $assign=compact('data');
        return $this->successJson($assign);
    }
    public function getAdd(){
        return $this->view('main.setting.add');
    }
    public function postAdd(){
        $this->validate($this->req, [
            'tid' => 'required|max:50|unique:settings',
            'type' => 'required|max:4',
            'params' => 'required',
        ]);
        $data=$this->req->except('_token');
        $user=Auth::guard('cp')->user();
        $data['user']=$user->name;
        $data['date']=date('Y-m-d H:i:s');
        $data['ip']=$this->req->ip();
        Setting::create($data);
        return $this->successJson([],'添加成功！');
    }
    public function getEdit(){
        $this->validate($this->req, [
            'id' => 'required|exists:settings,id',
        ]);
        $item=Setting::find($this->req->id);
        $assign=compact('item');
        return $this->successJson($assign,'添加成功！');
    }
    public function postEdit(){
        $this->validate($this->req, [
            'type' => 'required|max:4',
            'params' => 'required',
            'id' => 'required',
        ]);
        $data=$this->req->except('_token');
        $user=Auth::guard('cp')->user();
        $data['user']=$user->name;
        $data['date']=date('Y-m-d H:i:s');
        $data['ip']=$this->req->ip();
        Setting::whereId($this->req->id)->update($data);
        return $this->successJson([],'修改成功！');
    }

}
