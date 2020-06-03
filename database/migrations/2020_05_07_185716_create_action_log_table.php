<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('date')->nullable()->comment('时间');
            $table->char('guard',10)->comment('guard');
            $table->ipAddress('ip')->comment('ip');
            $table->string('uri')->comment('请求地址');
            $table->text('params')->comment('参数');
            $table->integer('user_id')->comment('uid');
            $table->char('user',50)->comment('uid');
            $table->char('http_type',10)->comment('http_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action_logs');
    }
}
