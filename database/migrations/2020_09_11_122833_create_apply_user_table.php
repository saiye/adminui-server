<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplyUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apply_user', function (Blueprint $table) {
            $table->id();
            $table->integer('area_code')->comment('地区码');
            $table->string('phone', 20)->comment('phone');
            $table->string('username', 20)->comment('用户名');
            $table->tinyInteger('status')->default(0)->comment('0未联系,1已电联');
            $table->tinyInteger('is_close')->default(0)->comment('0未处理,1已处理');
            $table->text('info')->comment('沟通结果！');
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
        Schema::dropIfExists('apply_user');
    }
}
