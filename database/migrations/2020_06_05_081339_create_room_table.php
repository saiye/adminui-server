<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room', function (Blueprint $table) {
            $table->increments('room_id')->comment('房间id');
            $table->string('room_name',30)->comment('房间名称');
            $table->unsignedInteger('seats_num')->comment('座位数');
            $table->unsignedInteger('store_id')->comment('所属店面');
            $table->unsignedInteger('game_plank_id')->comment('游戏板子id');
            $table->tinyInteger('voice_type')->comment('声音类型');
            $table->tinyInteger('desk_sort')->comment('桌子排序');
            $table->string('description',150)->nullable()->comment('房间描述');
            $table->tinyInteger('mode')->comment('胜负模式');
            $table->tinyInteger('card')->comment('明牌暗牌');
            $table->tinyInteger('sergeant')->comment('竞选警长');
            $table->json('time_set')->comment('时间设置json');
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
        Schema::dropIfExists('room');
    }
}
