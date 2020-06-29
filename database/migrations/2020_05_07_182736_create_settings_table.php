<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id')->comment('自增id');
            $table->string('tid',50)->comment('唯一标识');
            $table->timestamp('date')->comment('操作时间');
            $table->ipAddress('ip')->comment('ip地址');
            $table->string('user',50)->comment('用户名');
            $table->tinyInteger('type')->comment('设置类型');
            $table->text('params')->comment('设置参数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
