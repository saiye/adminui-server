<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //提现记录
        Schema::create('withdraw_log', function (Blueprint $table) {
            $table->id();
            $table->string('withdraw_no',80)->unique()->comment('提现订单号');
            $table->tinyInteger('check_status')->default(0)->comment('退款审核状态0未审核，1通过，2不通过,3用户撤销');
            $table->decimal('withdraw_fee',10,2)->default(0)->comment('提现金额');
            $table->decimal('remit_fee',10,2)->default(0)->comment('打款金额');
            $table->tinyInteger('pay_type')->default(0)->comment('打款方式');
            $table->unsignedInteger('company_id')->comment('商户id');
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
        Schema::dropIfExists('withdraw_log');
    }
}
