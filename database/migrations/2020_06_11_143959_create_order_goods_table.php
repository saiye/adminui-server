<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_goods', function (Blueprint $table) {
            $table->increments('order_goods_id')->comment('订单商品id');
            $table->unsignedInteger('order_id')->comment('订单id');
            $table->unsignedInteger('goods_id')->comment('商品id');
            $table->unsignedInteger('goods_num')->comment('购买数量');
            $table->decimal('goods_price',8,3)->comment('商品价格');
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
        Schema::dropIfExists('order_goods');
    }
}
