<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendCircle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //朋友圈信息
        Schema::create('friend_circle', function (Blueprint $table) {
            $table->increments('circle_id')->comment('circle_id');
            $table->bigInteger('user_id')->comment('user_id');
            $table->string('info')->comment('消息');
            $table->bigInteger('praise')->default(0)->comment('点赞人数');
            $table->bigInteger('comment')->default(0)->comment('评论人数');
            $table->tinyInteger('is_del')->default(0)->comment('删除状态');
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
        Schema::dropIfExists('friend_circle');
    }
}
