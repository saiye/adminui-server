<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->increments('staff_id')->comment('员工id');
            $table->char('account', 20)->unique()->comment('登录账号');
            $table->string('password')->comment('登录密码');
            $table->string('real_name',20)->nullable()->comment('真实姓名');
            $table->tinyInteger('sex')->default(3)->comment('性别1男,2女,3未知');
            $table->string('phone',11)->comment('手机号码');
            $table->tinyInteger('lock')->default(0)->comment('是否锁定1正常2锁定');
            $table->tinyInteger('type')->default(4)->comment('账号类型:1超级管理员,2管理员,3店长,4店员');
            $table->unsignedInteger('company_id')->default(0)->comment('所属公司id');
            $table->unsignedInteger('store_id')->default(0)->comment('所属店面id,0非店面人员');
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
        Schema::dropIfExists('staff');
    }
}
