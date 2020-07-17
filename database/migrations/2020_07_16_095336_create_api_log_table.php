<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_log', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('date')->nullable()->comment('时间');
            $table->ipAddress('ip')->comment('ip');
            $table->string('uri')->comment('请求地址');
            $table->text('params')->comment('参数');
            $table->text('response')->comment('响应参数！');
            $table->string('http_type', 10)->comment('http_type');
            $table->string('tag', 10)->comment('接口分组标记');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_log');
    }
}
