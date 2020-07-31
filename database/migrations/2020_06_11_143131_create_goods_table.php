<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->increments('goods_id')->comment('商品id');
            $table->string('goods_name',50)->comment('商品名称');
            $table->string('info',100)->comment('商品详情');
            $table->decimal('goods_price',8,3)->comment('商品单价');
            $table->tinyInteger('status')->default(1)->comment('商品状态1正常销售，2暂停销售');
            $table->unsignedInteger('store_id')->default(0)->comment('所属店面');
            $table->unsignedInteger('company_id')->default(0)->comment('所属公司id');
            $table->unsignedInteger('sku_id')->default(0)->comment('默认skuId');
            $table->unsignedInteger('stock')->default(0)->comment('库存');
            $table->unsignedInteger('daily_sales')->default(0)->comment('日销量');
            $table->unsignedInteger('monthly_sales')->default(0)->comment('月销量');
            $table->string('image',100)->nullable()->comment('商品图片');
            $table->string('tag',100)->nullable()->comment('默认规格字符');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.php
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods');
    }
}
