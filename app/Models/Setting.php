<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Config;
class Setting extends Model
{
    use ModelDataFormat;
    public   $timestamps=false;
    protected $table = 'settings';

    protected $guarded = [
        'id'
    ];


    public function type(){
        return Config::get('setting.type.'.$this->type);
    }

    public function checkRequest($request){
        return $this->checkSensitiveWord($request);
    }

    public function checkSensitiveWord($request){
        $res=Setting::whereIn('type',[1,2])->get();
        foreach ($request->except('_token','id','page','password') as $k=>$v){
            if(!is_array($v) and $v)
            foreach ($res as $set){
                foreach (explode('#',$set->params) as $filter){
                        $f=strpos($v,$filter);
                        if($f!==false){
                            return [false,$set->params];
                        }
                }
            }
        }
        return [true,'ok'];
    }



}
