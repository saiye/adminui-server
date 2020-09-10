<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('account', 50)->unique()->comment('登录账号');
            $table->string('password')->comment('登录密码');
            $table->string('real_name',20)->nullable()->comment('真实姓名');
            $table->string('nickname',20)->nullable()->comment('昵称');
            $table->string('email',30)->nullable()->comment('邮箱');
            $table->string('icon',100)->nullable()->comment('头像');
            $table->tinyInteger('sex')->default(1)->comment('性别0男,1女');
            $table->tinyInteger('judge')->default(2)->comment('是否为法官1是,2否');
            $table->tinyInteger('lock')->default(1)->comment('是否正常,1是2否');
            $table->unsignedInteger('level')->default(1)->comment('等级');
            $table->unsignedInteger('popularity')->default(0)->comment('人气');
            $table->unsignedInteger('attention')->default(0)->comment('关注');
            $table->unsignedInteger('fans')->default(0)->comment('粉丝');
            $table->decimal('remaining',10,2)->default(0)->comment('余额');
            $table->decimal('income',10,2)->default(0)->comment('收入');
            $table->decimal('withdrawal',10,2)->default(0)->comment('已提现');
            $table->integer('channel_id')->default(0)->comment('最后登录渠道');
            $table->tinyInteger('online')->default(0)->comment('是否在线0否1是');
            $table->tinyInteger('play')->default(0)->comment('是否在游戏0否1是');
            $table->tinyInteger('two_way')->default(0)->comment('是否仅双向好友发信息');
            $table->decimal('lon',10,6)->default(0)->comment('经度');
            $table->decimal('lat',8,6)->default(0)->comment('维度');
            $table->string('token',80)->unique()->nullable()->default(null)->comment('api token');
            $table->string('phone',20)->nullable()->comment('phone');
            $table->integer('area_code')->default(0)->comment('area_code');
            $table->unsignedInteger('parent_id')->default(0)->comment('父id');
            $table->string('open_id',50)->nullable()->comment('open_id');
            $table->tinyInteger('type')->default(0)->comment('用户类型1小程序');
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
        Schema::dropIfExists('users');
    }
}
