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
            $table->string('order_sn',80)->unique()->comment('订单号');
            $table->string('prepay_id',80)->comment('第三方订单号');
            $table->string('info',100)->comment('订单简讯');
            $table->decimal('total_price',10,2)->comment('订单总价');
            $table->decimal('due_price',10,2)->comment('应付金额');
            $table->decimal('actual_payment',10,2)->comment('真实支付');
            $table->unsignedInteger('user_id')->comment('用户id');
            $table->string('nickname',20)->comment('用户昵称');
            $table->string('phone',11)->comment('用户手机号');
            $table->unsignedInteger('room_id')->comment('所属房间');
            $table->unsignedInteger('store_id')->comment('所属店面');
            $table->unsignedInteger('company_id')->default(0)->comment('所属公司id');
            $table->unsignedInteger('staff_id')->comment('下单员工');
            $table->unsignedInteger('pay_time')->default(0)->comment('支付时间');
            $table->unsignedInteger('coupon_id')->default(0)->comment('券id');
            $table->decimal('coupon_price',10,2)->default(0)->comment('券减金额');
            $table->decimal('integral_price',10,2)->default(0)->comment('积分减额');
            $table->tinyInteger('pay_type')->default(0)->comment('支付方式');
            $table->tinyInteger('pay_status')->default(0)->comment('支付状态');
            $table->tinyInteger('status')->default(0)->comment('订单状态');
            $table->tinyInteger('is_abnormal')->default(0)->comment('是否为异常订单');
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
