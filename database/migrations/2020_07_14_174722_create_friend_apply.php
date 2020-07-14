<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendApply extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //好友申请
        Schema::create('friend_apply', function (Blueprint $table) {
            $table->increments('apply_id')->comment('apply_id');
            $table->char('game_id',32)->unique()->comment('game_id游戏唯一标识');
            $table->bigInteger('user_id')->comment('user_id');
            $table->bigInteger('friend_id')->comment('friend_id');
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
        Schema::dropIfExists('friend_apply');
    }
}
