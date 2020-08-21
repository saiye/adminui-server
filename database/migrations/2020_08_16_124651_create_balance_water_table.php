<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceWaterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_water', function (Blueprint $table) {
            $table->id();
            $table->string('balance_sn', 80)->unique()->comment('流水号');
            $table->string('order_sn', 80)->comment('订单号');
            $table->decimal('price', 10, 2)->default(0)->comment('变动金额');
            $table->tinyInteger('type')->default(0)->comment('0消费1充值');
            $table->tinyInteger('status')->default(0)->comment('0为成功1成功');
            $table->bigInteger('user_id')->default(0)->comment('user_id');
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
        Schema::dropIfExists('balance_water');
    }
}
