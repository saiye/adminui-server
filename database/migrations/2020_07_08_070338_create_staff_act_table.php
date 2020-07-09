<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffActTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_acts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('act')->comment('权限路由');
            $table->unsignedInteger('role_id')->comment('角色id');
            $table->unsignedInteger('company_id')->comment('商户id');
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
        Schema::dropIfExists('staff_acts');
    }
}
