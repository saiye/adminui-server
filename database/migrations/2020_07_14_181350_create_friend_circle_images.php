<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendCircleImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friend_circle_images', function (Blueprint $table) {
            $table->increments('image_id')->comment('image_id');
            $table->string('path')->comment('图片路径');
            $table->tinyInteger('is_del')->default(0)->comment('删除状态');
            $table->unsignedInteger('friend_circle_id')->comment('外键id');
            $table->unsignedInteger('user_id')->comment('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('friend_circle_images');
    }
}
