<?php
namespace App\Models;
use App\TraitInterface\ModelDataFormat;
use Config;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use Notifiable,ModelDataFormat;
    public  $primaryKey='staff_id';
    protected $table = 'staff';

    protected $guarded = [
        'staff_id'
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * 导航栏过滤
     */
    public function roleMenu(){

        $menus=Config::get('staff');

        $super_admin=Config::get('role.super_admin',[]);

        if(in_array($this->email,$super_admin)){
            return $menus;
        }
        $acts=$this->acts->pluck('act')->toArray();
        //权限过滤rbac
        $m=Config::get('auth.rbac',[]);
        foreach($menus as $k1=>&$sub){
            if(!in_array($sub['act'],$m) and !in_array($k1,$acts)){
                unset($menus[$k1]);
                continue;
            }
            foreach ($sub['child'] as $k2=>&$su1){
                if(!in_array($su1['act'],$m) and !in_array($k1.'.'.$k2,$acts)){
                    unset($sub['child'][$k2]);
                    continue;
                }
                foreach ($su1['child'] as $k3=>$child){
                    if(!in_array($child['act'],$m) and !in_array($child['url'],$acts)){
                        unset($su1['child'][$k3]);
                        continue;
                    }
                }
            }
        }
        return $menus;
    }
}
