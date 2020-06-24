<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->increments('order_id')->comment('order_id');
            $table->char('order_sn',15)->comment('订单号');
            $table->decimal('total_price',8,3)->comment('订单总价');
            $table->unsignedInteger('room_id')->comment('所属房间');
            $table->unsignedInteger('store_id')->comment('所属店面');
            $table->unsignedInteger('company_id')->default(0)->comment('所属公司id');
            $table->unsignedInteger('staff_id')->comment('下单员工');
            $table->unsignedInteger('play_time')->default(0)->comment('支付时间');
            $table->tinyInteger('play_type')->default(0)->comment('支付方式');
            $table->tinyInteger('play_status')->default(0)->comment('支付状态');
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
        Schema::dropIfExists('order');
    }
}
