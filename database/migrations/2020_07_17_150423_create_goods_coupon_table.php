<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_coupon', function (Blueprint $table) {
            $table->increments('coupon_id')->comment('coupon_id');
            $table->string('coupon_name',30)->comment('券名称');
            $table->decimal('condition_price',8,3)->comment('使用条件,满多少钱');
            $table->decimal('price',8,3)->comment("券面金额");
            $table->tinyInteger('is_del')->default(0)->comment('是否删除');
            $table->unsignedInteger('store_id')->default(0)->comment('店铺id');
            $table->unsignedInteger('count')->default(0)->comment('发行张数');
            $table->unsignedInteger('use_count')->default(0)->comment('使用张数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_coupon');
    }
}
