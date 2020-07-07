<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThreeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('three_users', function (Blueprint $table) {
            $table->id();
            $table->string('open_id', 100)->unique()->comment('openId');
            $table->tinyInteger('type')->default(1)->comment('type1小程序');
            $table->string('icon')->nullable()->comment('icon');
            $table->tinyInteger('sex')->comment('sex0未知，1男,2女');
            $table->bigInteger('user_id')->default(0)->comment('绑定的用户id!');
            $table->string('session_key', 100)->default('')->comment('session_key');
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
        Schema::dropIfExists('three_users');
    }
}
