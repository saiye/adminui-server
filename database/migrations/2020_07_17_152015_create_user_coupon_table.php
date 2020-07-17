<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_coupon', function (Blueprint $table) {
            $table->increments('user_coupon_id')->comment('user_ticket_id');
            $table->string('coupon_name')->comment('券名称');
            $table->decimal('condition_price',8,3)->comment('使用条件满多少钱');
            $table->decimal('price',8,3)->comment('券面金额');
            $table->tinyInteger('is_use')->default(0)->comment('是否使用0否1是');
            $table->unsignedInteger('store_id')->default(0)->comment('店铺id');
            $table->unsignedInteger('user_id')->default(0)->comment('user_id');
            $table->unsignedInteger('coupon_id')->default(0)->comment('券id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_coupon');
    }
}
