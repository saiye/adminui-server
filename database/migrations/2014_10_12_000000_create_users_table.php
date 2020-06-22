<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('account', 20)->unique()->comment('登录账号');
            $table->string('password')->comment('登录密码');
            $table->string('real_name',20)->nullable()->comment('真实姓名');
            $table->string('nickname',20)->nullable()->comment('昵称');
            $table->string('email',30)->nullable()->unique()->comment('邮箱');
            $table->string('icon',100)->nullable()->comment('头像');
            $table->tinyInteger('sex')->default(3)->comment('性别1男,2女,3未知');
            $table->tinyInteger('judge')->default(2)->comment('是否为法官1是,2否');
            $table->tinyInteger('lock')->default(1)->comment('是否正常,1是2否');
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
        Schema::dropIfExists('users');
    }
}
