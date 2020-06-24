<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device', function (Blueprint $table) {
            $table->increments('id')->comment('自增id');
            $table->integer('device_id')->unique()->comment('physics_address.id');
            $table->string('device_name',50)->comment('设备名称');
            $table->tinyInteger('seat_num')->comment('座位号');
            $table->unsignedInteger('room_id')->comment('所属房间');
            $table->unsignedInteger('store_id')->comment('所属店面');
            $table->unsignedInteger('company_id')->default(0)->comment('所属公司id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device');
    }
}
