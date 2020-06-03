<?php

use Illuminate\Database\Seeder;
use App\Models\CpUser;
use App\Models\CpRole;
use Illuminate\Support\Facades\Hash;

class CpUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->cerateRole();
        $this->registerAdmin();
    }

    public function registerAdmin(){
        $data=array();
        $data['role_id']=1;
        $data['email']=env('AdminEmail','714433615@qq.com');
        $data['username']=env('AdminUserName','admin');
        $data['password']=Hash::make(env('AdminPassword','123456'));
        $data['last_ip']='127.0.0.1';
        $data['current_ip']='127.0.0.1';
        $data['current_login_at']=date('Y-m-d H:i:s');
        $data['last_login_at']=date('Y-m-d H:i:s');
        $hasuser= CpUser::where('email','=',$data['email'])->first();
        if(!$hasuser){
            CpUser::create($data);
        }else{
            CpUser::where('username','=',$data['username'])->update($data);
        }
    }

    public function cerateRole(){
        $data=['role_name'=>'superuser','role_id'=>1];
        $admin=CpRole::find($data['role_id']);
        if(!$admin){
            CpRole::create($data);
        }
    }

}
