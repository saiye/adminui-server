<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area', function (Blueprint $table) {
            $table->increments('area_id')->comment('地区id');
            $table->string('area_code',20)->comment('地区码');
            $table->string('area_name',50)->comment('地区名');
            $table->tinyInteger('level');
            $table->string('city_code',20);
            $table->string('center',150);
            $table->bigInteger('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('area');
    }
}
