<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoginLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->increments('id')->comment('自增id');
            $table->timestamp('date')->nullable()->comment('时间');
            $table->ipAddress('ip')->nullable()->comment('ip');
            $table->unsignedInteger('user_id')->nullable()->comment('uid');
            $table->string('user',50)->nullable()->comment('username');
            $table->char('guard',10)->nullable()->comment('guard');
            $table->char('action_type',20)->nullable()->comment('action_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('login_logs');
    }
}
