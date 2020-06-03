<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpuserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cp_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_name',20)->unique()->comment('');
            $table->string('email',50)->comment('邮件地址');
            $table->integer('role_id')->comment('角色id');
            $table->string('password')->comment('');
            $table->boolean('lock')->default(0)->comment('是否锁定');
            $table->ipAddress('last_ip')->comment('last ip address');
            $table->ipAddress('current_ip')->comment('current ip address');
            $table->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            $table->timestamp('current_login_at')->nullable()->comment('当前登录时间');
            $table->string('avatar')->nullable()->comment('用户头像');
            $table->string('api_token',80)->unique()->nullable()->default(null)->comment('api token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cp_users');
    }
}
