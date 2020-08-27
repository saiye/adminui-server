<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoteSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('note_sms', function (Blueprint $table) {
            $table->id();
            $table->string('area_code', 10)->comment('地区码');
            $table->string('phone', 20)->comment('phone');
            $table->json('msg')->comment('msg');
            $table->bigInteger('create_time')->default(0)->comment('创建时间');
            $table->tinyInteger('status')->default(0)->comment('发送状态0未发送,1已发送');
            $table->string('type',100)->comment('类型1注册，2找回密码');
            $table->string('action',100)->comment('操作');
            $table->json('res')->comment('结果');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('note_sms');
    }

}
