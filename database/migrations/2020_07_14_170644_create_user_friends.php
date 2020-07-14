<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserFriends extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //好友
        Schema::create('user_friends', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->comment('user_id');
            $table->bigInteger('friend_id')->comment('friend_id');
            $table->bigInteger('time')->comment('创建时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_friends');
    }
}
