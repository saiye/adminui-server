<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room', function (Blueprint $table) {
            $table->increments('room_id')->comment('房间id');
            $table->string('room_name', 30)->comment('房间名称');
            $table->tinyInteger('seats_num')->default(0)->comment('座位数');
            $table->string('describe', 150)->nullable()->comment('房间描述');
            $table->unsignedInteger('billing_id')->comment('计费模式');
            $table->tinyInteger('is_use')->default(2)->comment('是否使用1是，2否');
            $table->tinyInteger('online')->default(2)->comment('是否上线1是，2否');
            $table->unsignedInteger('use_time')->default(0)->comment('开始使用的时间点');
            $table->unsignedInteger('store_id')->comment('所属店面');
            $table->unsignedInteger('company_id')->default(0)->comment('所属公司id');
            $table->unsignedInteger('dup_id')->default(1)->comment('游戏dup_id');
            $table->string('deviceMqttTopic', 100)->nullable()->comment('Mqtt主题');
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
        Schema::dropIfExists('room');
    }
}
