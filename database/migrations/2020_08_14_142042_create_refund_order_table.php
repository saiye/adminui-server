<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //退款订单
        Schema::create('refund_order', function (Blueprint $table) {
            $table->id();
            $table->string('refund_no',80)->unique()->comment('退款订单号');
            $table->unsignedInteger('order_id')->comment('原订单id');
            $table->decimal('refund_fee',10,2)->default(0)->comment('申请退款金额');
            $table->decimal('cash_fee',10,2)->default(0)->comment('实际退款金额');
            $table->tinyInteger('pay_type')->default(0)->comment('退款方式');
            $table->tinyInteger('refund_status')->default(0)->comment('退款状态0未退，1已退');
            $table->tinyInteger('check_status')->default(0)->comment('退款审核状态0未审核，1通过，2不通过');
            $table->tinyInteger('refund_reason_type')->comment('退款类型');
            $table->string('refund_reason')->comment('退款原因');
            $table->string('refund_id',32)->comment('支付平台退款单号');
            $table->unsignedInteger('user_id')->comment('退款用户id');
            $table->unsignedInteger('refund_time')->default(0)->comment('退款时间');
            $table->unsignedInteger('store_id')->default(0)->comment('所属店面');
            $table->unsignedInteger('company_id')->default(0)->comment('所属公司id');
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
        Schema::dropIfExists('refund_order');
    }
}
