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
            $table->string('goods_name')->comment('商品名称');
            $table->unsignedInteger('goods_id')->comment('商品id');
            $table->unsignedInteger('goods_num')->comment('购买数量');
            $table->decimal('goods_price', 8, 3)->comment('商品单价');
            //type:1普通商品,2房费
            $table->tinyInteger('type')->default(1)->comment('商品类型');
            $table->string('tag',100)->nullable()->comment('默认规格字符');
            $table->string('image',100)->nullable()->comment('商品图片');
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
