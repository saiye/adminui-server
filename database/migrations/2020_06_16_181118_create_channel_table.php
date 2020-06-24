<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel', function (Blueprint $table) {
            $table->increments('channel_id')->comment('渠道id');
            $table->string('channel_name',30)->comment('渠道名称');
            $table->string('gameSrvAddr',100)->comment('游戏服地址');
            $table->string('loginCallBackAddr',100)->comment('登录回调地址');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channel');
    }
}
